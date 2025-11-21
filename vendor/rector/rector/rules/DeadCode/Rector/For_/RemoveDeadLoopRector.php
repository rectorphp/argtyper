<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\For_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Stmt\Do_;
use Argtyper202511\PhpParser\Node\Stmt\For_;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PhpParser\Node\Stmt\While_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\DeadCode\SideEffect\SideEffectNodeDetector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\For_\RemoveDeadLoopRector\RemoveDeadLoopRectorTest
 */
final class RemoveDeadLoopRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\SideEffect\SideEffectNodeDetector
     */
    private $sideEffectNodeDetector;
    public function __construct(SideEffectNodeDetector $sideEffectNodeDetector)
    {
        $this->sideEffectNodeDetector = $sideEffectNodeDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove loop with no body', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($values)
    {
        for ($i=1; $i<count($values); ++$i) {
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($values)
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
        return [Do_::class, For_::class, Foreach_::class, While_::class];
    }
    /**
     * @param Do_|For_|Foreach_|While_ $node
     */
    public function refactor(Node $node) : ?int
    {
        if ($node->stmts !== []) {
            return null;
        }
        if ($node instanceof Do_ || $node instanceof While_) {
            $exprs = [$node->cond];
        } elseif ($node instanceof For_) {
            $exprs = \array_merge($node->init, $node->cond, $node->loop);
        } else {
            $exprs = [$node->expr, $node->valueVar];
        }
        foreach ($exprs as $expr) {
            if ($expr instanceof Assign) {
                $expr = $expr->expr;
            }
            if ($this->sideEffectNodeDetector->detect($expr)) {
                return null;
            }
        }
        return NodeVisitor::REMOVE_NODE;
    }
}
