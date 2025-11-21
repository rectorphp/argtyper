<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Guard;

use DateTimeInterface;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\Error;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Naming\Naming\ConflictingNameResolver;
use Argtyper202511\Rector\Naming\Naming\OverriddenExistingNamesResolver;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Argtyper202511\Rector\Util\StringUtils;
/**
 * This class check if a variable name change breaks existing code in class method
 */
final class BreakingVariableRenameGuard
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ConflictingNameResolver
     */
    private $conflictingNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\OverriddenExistingNamesResolver
     */
    private $overriddenExistingNamesResolver;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper
     */
    private $typeUnwrapper;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @var string
     * @see https://regex101.com/r/1pKLgf/1
     */
    public const AT_NAMING_REGEX = '#[\w+]At$#';
    public function __construct(BetterNodeFinder $betterNodeFinder, ConflictingNameResolver $conflictingNameResolver, NodeTypeResolver $nodeTypeResolver, OverriddenExistingNamesResolver $overriddenExistingNamesResolver, TypeUnwrapper $typeUnwrapper, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->conflictingNameResolver = $conflictingNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->overriddenExistingNamesResolver = $overriddenExistingNamesResolver;
        $this->typeUnwrapper = $typeUnwrapper;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function shouldSkipVariable(string $currentName, string $expectedName, $functionLike, Variable $variable): bool
    {
        // is the suffix? → also accepted
        $expectedNameCamelCase = ucfirst($expectedName);
        if (substr_compare($currentName, $expectedNameCamelCase, -strlen($expectedNameCamelCase)) === 0) {
            return \true;
        }
        if ($this->conflictingNameResolver->hasNameIsInFunctionLike($expectedName, $functionLike)) {
            return \true;
        }
        if (!$functionLike instanceof ArrowFunction && $this->overriddenExistingNamesResolver->hasNameInClassMethodForNew($currentName, $functionLike)) {
            return \true;
        }
        if ($this->isVariableAlreadyDefined($variable, $currentName)) {
            return \true;
        }
        if ($this->hasConflictVariable($functionLike, $expectedName)) {
            return \true;
        }
        return $functionLike instanceof Closure && $this->isUsedInClosureUsesName($expectedName, $functionLike);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $classMethod
     */
    public function shouldSkipParam(string $currentName, string $expectedName, $classMethod, Param $param): bool
    {
        // is the suffix? → also accepted
        $expectedNameCamelCase = ucfirst($expectedName);
        if (substr_compare($currentName, $expectedNameCamelCase, -strlen($expectedNameCamelCase)) === 0) {
            return \true;
        }
        $conflictingNames = $this->conflictingNameResolver->resolveConflictingVariableNamesForParam($classMethod);
        if (in_array($expectedName, $conflictingNames, \true)) {
            return \true;
        }
        if ($this->conflictingNameResolver->hasNameIsInFunctionLike($expectedName, $classMethod)) {
            return \true;
        }
        if ($this->overriddenExistingNamesResolver->hasNameInFunctionLikeForParam($expectedName, $classMethod)) {
            return \true;
        }
        if ($param->var instanceof Error) {
            return \true;
        }
        if ($this->isVariableAlreadyDefined($param->var, $currentName)) {
            return \true;
        }
        if ($this->isRamseyUuidInterface($param)) {
            return \true;
        }
        if ($this->isGenerator($param)) {
            return \true;
        }
        if ($this->isDateTimeAtNamingConvention($param)) {
            return \true;
        }
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->getStmts(), function (Node $node) use ($expectedName): bool {
            if (!$node instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node, $expectedName);
        });
    }
    private function isVariableAlreadyDefined(Variable $variable, string $currentVariableName): bool
    {
        $scope = $variable->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        $trinaryLogic = $scope->hasVariableType($currentVariableName);
        if ($trinaryLogic->yes()) {
            return \true;
        }
        return $trinaryLogic->maybe();
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function hasConflictVariable($functionLike, string $newName): bool
    {
        if ($functionLike instanceof ArrowFunction) {
            return $this->betterNodeFinder->hasInstanceOfName(array_merge([$functionLike->expr], $functionLike->params), Variable::class, $newName);
        }
        return $this->betterNodeFinder->hasInstanceOfName(array_merge((array) $functionLike->stmts, $functionLike->params), Variable::class, $newName);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function isUsedInClosureUsesName(string $expectedName, $functionLike): bool
    {
        if (!$functionLike instanceof Closure) {
            return \false;
        }
        return $this->betterNodeFinder->hasVariableOfName($functionLike->uses, $expectedName);
    }
    private function isRamseyUuidInterface(Param $param): bool
    {
        return $this->nodeTypeResolver->isObjectType($param, new ObjectType('Argtyper202511\Ramsey\Uuid\UuidInterface'));
    }
    private function isDateTimeAtNamingConvention(Param $param): bool
    {
        $type = $this->nodeTypeResolver->getType($param);
        $type = $this->typeUnwrapper->unwrapFirstObjectTypeFromUnionType($type);
        $className = ClassNameFromObjectTypeResolver::resolve($type);
        if ($className === null) {
            return \false;
        }
        if (!is_a($className, DateTimeInterface::class, \true)) {
            return \false;
        }
        /** @var string $currentName */
        $currentName = $this->nodeNameResolver->getName($param);
        return StringUtils::isMatch($currentName, self::AT_NAMING_REGEX);
    }
    private function isGenerator(Param $param): bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        $paramType = $this->nodeTypeResolver->getType($param);
        if (!$paramType instanceof ObjectType) {
            return \false;
        }
        if (substr_compare($paramType->getClassName(), 'Generator', -strlen('Generator')) === 0 || substr_compare($paramType->getClassName(), 'Iterator', -strlen('Iterator')) === 0) {
            return \true;
        }
        return $paramType->isInstanceOf('Argtyper202511\Symfony\Component\DependencyInjection\Argument\RewindableGenerator')->yes();
    }
}
