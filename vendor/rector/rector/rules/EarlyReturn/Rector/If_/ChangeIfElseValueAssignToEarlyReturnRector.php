<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\EarlyReturn\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\NodeManipulator\IfManipulator;
use Argtyper202511\Rector\NodeManipulator\StmtsManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector\ChangeIfElseValueAssignToEarlyReturnRectorTest
 */
final class ChangeIfElseValueAssignToEarlyReturnRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\StmtsManipulator
     */
    private $stmtsManipulator;
    public function __construct(IfManipulator $ifManipulator, StmtsManipulator $stmtsManipulator)
    {
        $this->ifManipulator = $ifManipulator;
        $this->stmtsManipulator = $stmtsManipulator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change if/else value to early return', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            $docToken = $tokens[$this->getDocBlockIndex($tokens, $index)];
        } else {
            $docToken = null;
        }

        return $docToken;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($this->hasDocBlock($tokens, $index)) {
            return $tokens[$this->getDocBlockIndex($tokens, $index)];
        }
        return null;
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?StmtsAwareInterface
    {
        if ($node->stmts === null) {
            return null;
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Return_) {
                continue;
            }
            if (!$stmt->expr instanceof Expr) {
                continue;
            }
            $previousStmt = $node->stmts[$key - 1] ?? null;
            if (!$previousStmt instanceof If_) {
                continue;
            }
            $if = $previousStmt;
            if (!$this->ifManipulator->isIfAndElseWithSameVariableAssignAsLastStmts($if, $stmt->expr)) {
                continue;
            }
            end($if->stmts);
            $lastIfStmtKey = key($if->stmts);
            reset($if->stmts);
            /** @var Assign $assign */
            $assign = $this->stmtsManipulator->getUnwrappedLastStmt($if->stmts);
            $returnLastIf = new Return_($assign->expr);
            $this->mirrorComments($returnLastIf, $assign);
            $if->stmts[$lastIfStmtKey] = $returnLastIf;
            /** @var Else_ $else */
            $else = $if->else;
            /** @var array<int, Stmt> $elseStmts */
            $elseStmts = $else->stmts;
            /** @var Assign $assign */
            $assign = $this->stmtsManipulator->getUnwrappedLastStmt($elseStmts);
            $this->mirrorComments($stmt, $assign);
            $if->else = null;
            $stmt->expr = $assign->expr;
            $lastStmt = array_pop($node->stmts);
            $elseStmtsExceptLast = array_slice($elseStmts, 0, -1);
            $node->stmts = array_merge($node->stmts, $elseStmtsExceptLast, [$lastStmt]);
            return $node;
        }
        return null;
    }
}
