<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Exit_;
use Argtyper202511\PhpParser\Node\Expr\Throw_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Break_;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\Continue_;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Finally_;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Goto_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\InlineHTML;
use Argtyper202511\PhpParser\Node\Stmt\Label;
use Argtyper202511\PhpParser\Node\Stmt\Namespace_;
use Argtyper202511\PhpParser\Node\Stmt\Nop;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\PhpParser\Node\Stmt\TryCatch;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
final class TerminatedNodeAnalyzer
{
    /**
     * @var array<class-string<Node>>
     */
    private const TERMINABLE_NODES = [Return_::class, Break_::class, Continue_::class];
    /**
     * @var array<class-string<Node>>
     */
    private const TERMINABLE_NODES_BY_ITS_STMTS = [TryCatch::class, If_::class, Switch_::class];
    /**
     * @var array<class-string<Node>>
     */
    private const ALLOWED_CONTINUE_CURRENT_STMTS = [InlineHTML::class, Nop::class];
    public function isAlwaysTerminated(StmtsAwareInterface $stmtsAware, Stmt $node, Stmt $currentStmt): bool
    {
        if (in_array(get_class($currentStmt), self::ALLOWED_CONTINUE_CURRENT_STMTS, \true)) {
            return \false;
        }
        if (($stmtsAware instanceof FileWithoutNamespace || $stmtsAware instanceof Namespace_) && ($currentStmt instanceof ClassLike || $currentStmt instanceof Function_)) {
            return \false;
        }
        if (!in_array(get_class($node), self::TERMINABLE_NODES_BY_ITS_STMTS, \true)) {
            return $this->isTerminatedNode($node, $currentStmt);
        }
        if ($node instanceof TryCatch) {
            return $this->isTerminatedInLastStmtsTryCatch($node, $currentStmt);
        }
        if ($node instanceof If_) {
            return $this->isTerminatedInLastStmtsIf($node, $currentStmt);
        }
        /** @var Switch_ $node */
        return $this->isTerminatedInLastStmtsSwitch($node, $currentStmt);
    }
    private function isTerminatedNode(Node $previousNode, Node $currentStmt): bool
    {
        if (in_array(get_class($previousNode), self::TERMINABLE_NODES, \true)) {
            return \true;
        }
        if ($previousNode instanceof Expression && ($previousNode->expr instanceof Exit_ || $previousNode->expr instanceof Throw_)) {
            return \true;
        }
        if ($previousNode instanceof Goto_) {
            return !$currentStmt instanceof Label;
        }
        return \false;
    }
    private function isTerminatedInLastStmtsSwitch(Switch_ $switch, Stmt $stmt): bool
    {
        if ($switch->cases === []) {
            return \false;
        }
        $hasDefault = \false;
        foreach ($switch->cases as $key => $case) {
            if (!$case->cond instanceof Expr) {
                $hasDefault = \true;
            }
            if ($case->stmts === [] && isset($switch->cases[$key + 1])) {
                continue;
            }
            if (!$this->isTerminatedInLastStmts($case->stmts, $stmt)) {
                return \false;
            }
        }
        return $hasDefault;
    }
    private function isTerminatedInLastStmtsTryCatch(TryCatch $tryCatch, Stmt $stmt): bool
    {
        if ($tryCatch->finally instanceof Finally_ && $this->isTerminatedInLastStmts($tryCatch->finally->stmts, $stmt)) {
            return \true;
        }
        foreach ($tryCatch->catches as $catch) {
            if (!$this->isTerminatedInLastStmts($catch->stmts, $stmt)) {
                return \false;
            }
        }
        return $this->isTerminatedInLastStmts($tryCatch->stmts, $stmt);
    }
    private function isTerminatedInLastStmtsIf(If_ $if, Stmt $stmt): bool
    {
        // Without ElseIf_[] and Else_, after If_ is possibly executable
        if ($if->elseifs === [] && !$if->else instanceof Else_) {
            return \false;
        }
        foreach ($if->elseifs as $elseif) {
            if (!$this->isTerminatedInLastStmts($elseif->stmts, $stmt)) {
                return \false;
            }
        }
        if (!$this->isTerminatedInLastStmts($if->stmts, $stmt)) {
            return \false;
        }
        if (!$if->else instanceof Else_) {
            return \false;
        }
        return $this->isTerminatedInLastStmts($if->else->stmts, $stmt);
    }
    /**
     * @param Stmt[] $stmts
     */
    private function isTerminatedInLastStmts(array $stmts, Node $node): bool
    {
        if ($stmts === []) {
            return \false;
        }
        end($stmts);
        $lastKey = key($stmts);
        reset($stmts);
        $lastNode = $stmts[$lastKey];
        if (isset($stmts[$lastKey - 1]) && !$this->isTerminatedNode($stmts[$lastKey - 1], $node)) {
            return \false;
        }
        if ($lastNode instanceof Expression) {
            return $lastNode->expr instanceof Exit_ || $lastNode->expr instanceof Throw_;
        }
        return $lastNode instanceof Return_;
    }
}
