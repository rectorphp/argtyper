<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Argtyper202511\Rector\Naming\Guard\BreakingVariableRenameGuard;
use Argtyper202511\Rector\Naming\Naming\ExpectedNameResolver;
use Argtyper202511\Rector\Naming\ParamRenamer\ParamRenamer;
use Argtyper202511\Rector\Naming\ValueObject\ParamRename;
use Argtyper202511\Rector\Naming\ValueObjectFactory\ParamRenameFactory;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\Skipper\FileSystem\PathNormalizer;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\RenameParamToMatchTypeRectorTest
 */
final class RenameParamToMatchTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Naming\Guard\BreakingVariableRenameGuard
     */
    private $breakingVariableRenameGuard;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ExpectedNameResolver
     */
    private $expectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver
     */
    private $matchParamTypeExpectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\ValueObjectFactory\ParamRenameFactory
     */
    private $paramRenameFactory;
    /**
     * @readonly
     * @var \Rector\Naming\ParamRenamer\ParamRenamer
     */
    private $paramRenamer;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(BreakingVariableRenameGuard $breakingVariableRenameGuard, ExpectedNameResolver $expectedNameResolver, MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver, ParamRenameFactory $paramRenameFactory, ParamRenamer $paramRenamer, ReflectionResolver $reflectionResolver)
    {
        $this->breakingVariableRenameGuard = $breakingVariableRenameGuard;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
        $this->paramRenameFactory = $paramRenameFactory;
        $this->paramRenamer = $paramRenamer;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename param to match ClassType', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $pie)
    {
        $food = $pie;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run(Apple $apple)
    {
        $food = $apple;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class];
    }
    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        $this->hasChanged = \false;
        foreach ($node->params as $param) {
            // skip as array-like
            if ($param->variadic) {
                continue;
            }
            if ($param->type === null) {
                continue;
            }
            if ($node instanceof ClassMethod && $this->shouldSkipClassMethodFromVendor($node)) {
                return null;
            }
            $expectedName = $this->expectedNameResolver->resolveForParamIfNotYet($param);
            if ($expectedName === null) {
                continue;
            }
            if ($this->shouldSkipParam($param, $expectedName, $node)) {
                continue;
            }
            $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($expectedName === null) {
                continue;
            }
            $paramRename = $this->paramRenameFactory->createFromResolvedExpectedName($node, $param, $expectedName);
            if (!$paramRename instanceof ParamRename) {
                continue;
            }
            $this->paramRenamer->rename($paramRename);
            $this->hasChanged = \true;
        }
        if (!$this->hasChanged) {
            return null;
        }
        return $node;
    }
    private function shouldSkipClassMethodFromVendor(ClassMethod $classMethod) : bool
    {
        if ($classMethod->isPrivate()) {
            return \false;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        $ancestors = \array_filter($classReflection->getAncestors(), function (ClassReflection $ancestorClassReflection) use($classReflection) : bool {
            return $classReflection->getName() !== $ancestorClassReflection->getName();
        });
        $methodName = $this->getName($classMethod);
        foreach ($ancestors as $ancestor) {
            // internal
            if ($ancestor->getFileName() === null) {
                continue;
            }
            if (!$ancestor->hasNativeMethod($methodName)) {
                continue;
            }
            $path = PathNormalizer::normalize($ancestor->getFileName());
            if (\strpos($path, '/vendor/') !== \false) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $classMethod
     */
    private function shouldSkipParam(Param $param, string $expectedName, $classMethod) : bool
    {
        /** @var string $paramName */
        $paramName = $this->getName($param);
        if ($this->breakingVariableRenameGuard->shouldSkipParam($paramName, $expectedName, $classMethod, $param)) {
            return \true;
        }
        if (!$classMethod instanceof ClassMethod) {
            return \false;
        }
        // promoted property
        if (!$this->isName($classMethod, MethodName::CONSTRUCT)) {
            return \false;
        }
        return $param->isPromoted();
    }
}
