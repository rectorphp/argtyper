<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\ClassMethod;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassMethod\DowngradeAbstractPrivateMethodInTraitRector\DowngradeAbstractPrivateMethodInTraitRectorTest
 */
final class DowngradeAbstractPrivateMethodInTraitRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove "abstract" from private methods in traits and adds an empty function body', [new CodeSample(<<<'CODE_SAMPLE'
trait SomeTrait
{
    abstract private function someAbstractPrivateFunction();
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
trait SomeTrait
{
    private function someAbstractPrivateFunction() {}
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        // remove abstract
        $node->flags -= Modifiers::ABSTRACT;
        // Add empty array for stmts to generate empty function body
        $node->stmts = [];
        return $node;
    }
    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (!$classMethod->isAbstract()) {
            return \true;
        }
        if (!$classMethod->isPrivate()) {
            return \true;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($classMethod);
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        return !$classReflection->isTrait();
    }
}
