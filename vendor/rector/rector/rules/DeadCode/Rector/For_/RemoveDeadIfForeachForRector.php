<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\For_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\For_;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\EarlyReturn\NodeTransformer\ConditionInverter;
use Argtyper202511\Rector\NodeManipulator\StmtsManipulator;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\For_\RemoveDeadIfForeachForRector\RemoveDeadIfForeachForRectorTest
 */
final class RemoveDeadIfForeachForRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\EarlyReturn\NodeTransformer\ConditionInverter
     */
    private $conditionInverter;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ConditionInverter $conditionInverter, BetterNodeFinder $betterNodeFinder, StmtsManipulator $stmtsManipulator)
    {
        $this->conditionInverter = $conditionInverter;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove if, foreach and for that does not do anything', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value, $differentValue)
    {
        if ($value) {
        }

        foreach ($values as $value) {
        }

        return $differentValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value, $differentValue)
    {
        return $differentValue;
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node) : ?\Argtyper202511\PhpParser\Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $this->hasChanged = \false;
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof If_ && !$stmt instanceof For_ && !$stmt instanceof Foreach_) {
                continue;
            }
            if ($stmt->stmts !== []) {
                continue;
            }
            if ($stmt instanceof If_) {
                $this->processIf($stmt, $key, $node);
                continue;
            }
            $this->processForForeach($stmt, $key, $node);
        }
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function processIf(If_ $if, int $key, StmtsAwareInterface $stmtsAware) : void
    {
        if ($if->elseifs !== []) {
            return;
        }
        // useless if ()
        if (!$if->else instanceof Else_) {
            if ($this->hasNodeSideEffect($if->cond)) {
                return;
            }
            unset($stmtsAware->stmts[$key]);
            $this->hasChanged = \true;
            return;
        }
        $if->cond = $this->conditionInverter->createInvertedCondition($if->cond);
        $if->stmts = $if->else->stmts;
        $if->else = null;
        $this->hasChanged = \true;
    }
    /**
     * @param \PhpParser\Node\Stmt\For_|\PhpParser\Node\Stmt\Foreach_ $for
     */
    private function processForForeach($for, int $key, StmtsAwareInterface $stmtsAware) : void
    {
        if ($for instanceof For_) {
            $variables = $this->betterNodeFinder->findInstanceOf(\array_merge($for->init, $for->cond, $for->loop), Variable::class);
            foreach ($variables as $variable) {
                if ($this->stmtsManipulator->isVariableUsedInNextStmt($stmtsAware, $key + 1, (string) $this->getName($variable))) {
                    return;
                }
            }
            unset($stmtsAware->stmts[$key]);
            $this->hasChanged = \true;
            return;
        }
        $exprs = [$for->expr, $for->valueVar, $for->valueVar];
        $variables = $this->betterNodeFinder->findInstanceOf($exprs, Variable::class);
        foreach ($variables as $variable) {
            if ($this->stmtsManipulator->isVariableUsedInNextStmt($stmtsAware, $key + 1, (string) $this->getName($variable))) {
                return;
            }
        }
        unset($stmtsAware->stmts[$key]);
        $this->hasChanged = \true;
    }
    private function hasNodeSideEffect(Expr $expr) : bool
    {
        return $this->betterNodeFinder->hasInstancesOf($expr, [CallLike::class, Assign::class]);
    }
}
