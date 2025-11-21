<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Empty_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\Empty_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\Php\ReservedKeywordAnalyzer;
use Argtyper202511\Rector\PhpParser\AstResolver;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\AllAssignNodePropertyTypeInferer;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector\SimplifyEmptyCheckOnEmptyArrayRectorTest
 */
final class SimplifyEmptyCheckOnEmptyArrayRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\AllAssignNodePropertyTypeInferer
     */
    private $allAssignNodePropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\Php\ReservedKeywordAnalyzer
     */
    private $reservedKeywordAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer, ReflectionResolver $reflectionResolver, AstResolver $astResolver, AllAssignNodePropertyTypeInferer $allAssignNodePropertyTypeInferer, ReservedKeywordAnalyzer $reservedKeywordAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
        $this->reflectionResolver = $reflectionResolver;
        $this->astResolver = $astResolver;
        $this->allAssignNodePropertyTypeInferer = $allAssignNodePropertyTypeInferer;
        $this->reservedKeywordAnalyzer = $reservedKeywordAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Simplify empty() functions calls on empty arrays', [new CodeSample(<<<'CODE_SAMPLE'
$array = [];

if (empty($values)) {
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$array = [];

if ([] === $values) {
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Empty_::class, BooleanNot::class];
    }
    /**
     * @param Empty_|BooleanNot $node $node
     */
    public function refactor(Node $node): ?Node
    {
        $scope = ScopeFetcher::fetch($node);
        if ($node instanceof BooleanNot) {
            if ($node->expr instanceof Empty_ && $this->isAllowedExpr($node->expr->expr, $scope)) {
                return new NotIdentical($node->expr->expr, new Array_());
            }
            return null;
        }
        if (!$this->isAllowedExpr($node->expr, $scope)) {
            return null;
        }
        return new Identical($node->expr, new Array_());
    }
    private function isAllowedVariable(Variable $variable): bool
    {
        if (is_string($variable->name) && $this->reservedKeywordAnalyzer->isNativeVariable($variable->name)) {
            return \false;
        }
        return !$this->exprAnalyzer->isNonTypedFromParam($variable);
    }
    private function isAllowedExpr(Expr $expr, Scope $scope): bool
    {
        if (!$scope->getType($expr)->isArray()->yes()) {
            return \false;
        }
        if ($expr instanceof Variable) {
            return $this->isAllowedVariable($expr);
        }
        if (!$expr instanceof PropertyFetch && !$expr instanceof StaticPropertyFetch) {
            return \false;
        }
        if (!$expr->name instanceof Identifier) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflectionSourceObject($expr);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $propertyName = $expr->name->toString();
        if (!$classReflection->hasNativeProperty($propertyName)) {
            return \false;
        }
        $phpPropertyReflection = $classReflection->getNativeProperty($propertyName);
        $nativeType = $phpPropertyReflection->getNativeType();
        if (!$nativeType instanceof MixedType) {
            return $nativeType->isArray()->yes();
        }
        $property = $this->astResolver->resolvePropertyFromPropertyReflection($phpPropertyReflection);
        /**
         * Skip property promotion mixed type for now, as:
         *
         *   - require assign in default param check
         *   - check all assign of property promotion params under the class
         */
        if (!$property instanceof Property) {
            return \false;
        }
        $type = $this->allAssignNodePropertyTypeInferer->inferProperty($property, $classReflection, $this->file);
        if (!$type instanceof Type) {
            return \false;
        }
        return $type->isArray()->yes();
    }
}
