<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\StaticCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Enum\ObjectReference;
use Argtyper202511\Rector\NodeAnalyzer\ClassAnalyzer;
use Argtyper202511\Rector\NodeManipulator\ClassMethodManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector\RemoveParentCallWithoutParentRectorTest
 */
final class RemoveParentCallWithoutParentRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassMethodManipulator
     */
    private $classMethodManipulator;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ClassMethodManipulator $classMethodManipulator, ClassAnalyzer $classAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->classMethodManipulator = $classMethodManipulator;
        $this->classAnalyzer = $classAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove unused parent call with no parent class', [new CodeSample(<<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
         parent::__construct();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class OrphanClass
{
    public function __construct()
    {
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
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($classMethod->stmts === null) {
                continue;
            }
            foreach ($classMethod->stmts as $key => $stmt) {
                if (!$stmt instanceof Expression) {
                    continue;
                }
                if ($stmt->expr instanceof StaticCall && $this->isParentStaticCall($stmt->expr)) {
                    if ($this->doesCalledMethodExistInParent($stmt->expr, $node)) {
                        continue;
                    }
                    unset($classMethod->stmts[$key]);
                    $hasChanged = \true;
                }
                if ($stmt->expr instanceof Assign) {
                    $assign = $stmt->expr;
                    if ($assign->expr instanceof StaticCall && $this->isParentStaticCall($assign->expr)) {
                        $staticCall = $assign->expr;
                        // is valid call
                        if ($this->doesCalledMethodExistInParent($staticCall, $node)) {
                            continue;
                        }
                        $assign->expr = $this->nodeFactory->createNull();
                        $hasChanged = \true;
                    }
                }
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isParentStaticCall(Expr $expr) : bool
    {
        if (!$expr instanceof StaticCall) {
            return \false;
        }
        if ($expr->name instanceof Expr) {
            return \false;
        }
        return $this->isName($expr->class, ObjectReference::PARENT);
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // skip cases when parent class reflection is not found
        if ($class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString())) {
            return \true;
        }
        // currently the classMethodManipulator isn't able to find usages of anonymous classes
        return $this->classAnalyzer->isAnonymousClass($class);
    }
    private function doesCalledMethodExistInParent(StaticCall $staticCall, Class_ $class) : bool
    {
        if (!$class->extends instanceof Name) {
            return \false;
        }
        $calledMethodName = $this->getName($staticCall->name);
        if (!\is_string($calledMethodName)) {
            return \false;
        }
        return $this->classMethodManipulator->hasParentMethodOrInterfaceMethod($class, $calledMethodName);
    }
}
