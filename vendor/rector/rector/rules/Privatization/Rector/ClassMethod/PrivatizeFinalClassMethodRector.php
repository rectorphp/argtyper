<?php

declare (strict_types=1);
namespace Rector\Privatization\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Privatization\Guard\LaravelModelGuard;
use Rector\Privatization\Guard\OverrideByParentClassGuard;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector\PrivatizeFinalClassMethodRectorTest
 */
final class PrivatizeFinalClassMethodRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\VisibilityGuard\ClassMethodVisibilityGuard
     */
    private $classMethodVisibilityGuard;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Privatization\Guard\OverrideByParentClassGuard
     */
    private $overrideByParentClassGuard;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Privatization\Guard\LaravelModelGuard
     */
    private $laravelModelGuard;
    public function __construct(ClassMethodVisibilityGuard $classMethodVisibilityGuard, VisibilityManipulator $visibilityManipulator, OverrideByParentClassGuard $overrideByParentClassGuard, BetterNodeFinder $betterNodeFinder, LaravelModelGuard $laravelModelGuard)
    {
        $this->classMethodVisibilityGuard = $classMethodVisibilityGuard;
        $this->visibilityManipulator = $visibilityManipulator;
        $this->overrideByParentClassGuard = $overrideByParentClassGuard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->laravelModelGuard = $laravelModelGuard;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change protected class method to private if possible', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    protected function someMethod()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private function someMethod()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->isFinal()) {
            return null;
        }
        if (!$this->overrideByParentClassGuard->isLegal($node)) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->shouldSkipClassMethod($classMethod)) {
                continue;
            }
            if ($this->laravelModelGuard->isProtectedMethod($classReflection, $classMethod)) {
                continue;
            }
            if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByParent($classMethod, $classReflection)) {
                continue;
            }
            if ($this->classMethodVisibilityGuard->isClassMethodVisibilityGuardedByTrait($classMethod, $classReflection)) {
                continue;
            }
            $this->visibilityManipulator->makePrivate($classMethod);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipClassMethod(ClassMethod $classMethod): bool
    {
        // edge case in nette framework
        /** @var string $methodName */
        $methodName = $this->getName($classMethod->name);
        if (strncmp($methodName, 'createComponent', strlen('createComponent')) === 0) {
            return \true;
        }
        if (!$classMethod->isProtected()) {
            return \true;
        }
        if ($classMethod->isMagic()) {
            return \true;
        }
        // if has parent call, its probably overriding parent one → skip it
        $hasParentCall = (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node): bool {
            if (!$node instanceof StaticCall) {
                return \false;
            }
            return $this->isName($node->class, 'parent');
        });
        return $hasParentCall;
    }
}
