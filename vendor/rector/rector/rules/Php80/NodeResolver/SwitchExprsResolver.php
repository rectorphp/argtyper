<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\NodeResolver;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Throw_;
use Argtyper202511\PhpParser\Node\Stmt\Break_;
use Argtyper202511\PhpParser\Node\Stmt\Case_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\Rector\Php80\Enum\MatchKind;
use Argtyper202511\Rector\Php80\ValueObject\CondAndExpr;
final class SwitchExprsResolver
{
    /**
     * @return CondAndExpr[]
     */
    public function resolve(Switch_ $switch): array
    {
        $newSwitch = clone $switch;
        $condAndExpr = [];
        $collectionEmptyCasesCond = [];
        if (!$this->areCasesValid($newSwitch)) {
            return [];
        }
        $this->moveDefaultCaseToLast($newSwitch);
        foreach ($newSwitch->cases as $key => $case) {
            if ($case->stmts !== []) {
                continue;
            }
            if (!$case->cond instanceof Expr) {
                return [];
            }
            $collectionEmptyCasesCond[$key] = $case->cond;
        }
        foreach ($newSwitch->cases as $key => $case) {
            if ($case->stmts === []) {
                continue;
            }
            $expr = $case->stmts[0];
            $comments = $expr->getComments();
            if ($expr instanceof Expression) {
                $expr = $expr->expr;
            }
            $condExprs = [];
            if ($case->cond instanceof Expr) {
                $emptyCasesCond = [];
                foreach ($collectionEmptyCasesCond as $i => $collectionEmptyCaseCond) {
                    if ($i > $key) {
                        break;
                    }
                    $emptyCasesCond[$i] = $collectionEmptyCaseCond;
                    unset($collectionEmptyCasesCond[$i]);
                }
                $condExprs = $emptyCasesCond;
                $condExprs[] = $case->cond;
            }
            if ($expr instanceof Throw_) {
                $condAndExpr[] = new CondAndExpr($condExprs, $expr, MatchKind::THROW, $comments);
            } elseif ($expr instanceof Return_) {
                $returnedExpr = $expr->expr;
                if (!$returnedExpr instanceof Expr) {
                    return [];
                }
                $condAndExpr[] = new CondAndExpr($condExprs, $returnedExpr, MatchKind::RETURN, $comments);
            } elseif ($expr instanceof Assign) {
                $condAndExpr[] = new CondAndExpr($condExprs, $expr, MatchKind::ASSIGN, $comments);
            } elseif ($expr instanceof Expr) {
                $condAndExpr[] = new CondAndExpr($condExprs, $expr, MatchKind::NORMAL, $comments);
            } else {
                return [];
            }
        }
        return $condAndExpr;
    }
    private function moveDefaultCaseToLast(Switch_ $switch): void
    {
        foreach ($switch->cases as $key => $case) {
            if ($case->cond instanceof Expr) {
                continue;
            }
            // not has next? default is at the end, no need move
            if (!isset($switch->cases[$key + 1])) {
                return;
            }
            // current default has no stmt? keep as is as rely to next case
            if ($case->stmts === []) {
                return;
            }
            for ($loop = $key - 1; $loop >= 0; --$loop) {
                if ($switch->cases[$loop]->stmts !== []) {
                    break;
                }
                unset($switch->cases[$loop]);
            }
            $caseToMove = $switch->cases[$key];
            unset($switch->cases[$key]);
            $switch->cases[] = $caseToMove;
            break;
        }
    }
    private function isValidCase(Case_ $case): bool
    {
        // prepend to previous one
        if ($case->stmts === []) {
            return \true;
        }
        if (count($case->stmts) === 2 && $case->stmts[1] instanceof Break_) {
            return \true;
        }
        // default throws stmts
        if (count($case->stmts) !== 1) {
            return \false;
        }
        // throws expression
        if ($case->stmts[0] instanceof Throw_) {
            return \true;
        }
        // instant return
        if ($case->stmts[0] instanceof Return_) {
            return \true;
        }
        // default value
        return !$case->cond instanceof Expr;
    }
    private function areCasesValid(Switch_ $newSwitch): bool
    {
        foreach ($newSwitch->cases as $case) {
            if (!$this->isValidCase($case)) {
                return \false;
            }
        }
        return \true;
    }
}
