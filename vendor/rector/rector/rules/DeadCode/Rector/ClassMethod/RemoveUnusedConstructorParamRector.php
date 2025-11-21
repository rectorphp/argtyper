<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\DeadCode\NodeManipulator\ClassMethodParamRemover;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\RemoveUnusedConstructorParamRectorTest
 */
final class RemoveUnusedConstructorParamRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeManipulator\ClassMethodParamRemover
     */
    private $classMethodParamRemover;
    public function __construct(ReflectionResolver $reflectionResolver, ClassMethodParamRemover $classMethodParamRemover)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->classMethodParamRemover = $classMethodParamRemover;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter in constructor', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey, $man)
    {
        $this->hey = $hey;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey)
    {
        $this->hey = $hey;
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $constructorClassMethod = $node->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($constructorClassMethod->params === []) {
            return null;
        }
        if ($constructorClassMethod->isAbstract()) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $interfaces = $classReflection->getInterfaces();
        foreach ($interfaces as $interface) {
            if ($interface->hasNativeMethod(MethodName::CONSTRUCT)) {
                return null;
            }
        }
        $changedConstructorClassMethod = $this->classMethodParamRemover->processRemoveParams($constructorClassMethod);
        if (!$changedConstructorClassMethod instanceof ClassMethod) {
            return null;
        }
        return $node;
    }
}
