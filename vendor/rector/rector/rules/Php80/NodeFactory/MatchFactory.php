<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\NodeFactory;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Match_;
use Argtyper202511\PhpParser\Node\Expr\Throw_;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\Php80\Enum\MatchKind;
use Argtyper202511\Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer;
use Argtyper202511\Rector\Php80\ValueObject\CondAndExpr;
use Argtyper202511\Rector\Php80\ValueObject\MatchResult;
use Argtyper202511\Rector\PhpParser\Comparing\NodeComparator;
final class MatchFactory
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\MatchArmsFactory
     */
    private $matchArmsFactory;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\MatchSwitchAnalyzer
     */
    private $matchSwitchAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(\Argtyper202511\Rector\Php80\NodeFactory\MatchArmsFactory $matchArmsFactory, MatchSwitchAnalyzer $matchSwitchAnalyzer, NodeComparator $nodeComparator)
    {
        $this->matchArmsFactory = $matchArmsFactory;
        $this->matchSwitchAnalyzer = $matchSwitchAnalyzer;
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    public function createFromCondAndExprs(Expr $condExpr, array $condAndExprs, ?Stmt $nextStmt) : ?MatchResult
    {
        $shouldRemoteNextStmt = \false;
        // is default value missing? maybe it can be found in next stmt
        if (!$this->matchSwitchAnalyzer->hasCondsAndExprDefaultValue($condAndExprs)) {
            // 1. is followed by throws stmts?
            if ($nextStmt instanceof Expression && $nextStmt->expr instanceof Throw_) {
                $throw = $nextStmt->expr;
                $condAndExprs[] = new CondAndExpr([], $throw, MatchKind::RETURN);
                $shouldRemoteNextStmt = \true;
            }
            // 2. is followed by return expr
            // implicit return default after switch
            if ($nextStmt instanceof Return_ && $nextStmt->expr instanceof Expr) {
                // @todo this should be improved
                $expr = $this->resolveAssignVar($condAndExprs);
                if ($expr instanceof ArrayDimFetch) {
                    return null;
                }
                if ($expr instanceof Expr && !$this->nodeComparator->areNodesEqual($nextStmt->expr, $expr)) {
                    return null;
                }
                $shouldRemoteNextStmt = !$expr instanceof Expr;
                $condAndExprs[] = new CondAndExpr([], $nextStmt->expr, MatchKind::RETURN, $nextStmt->getComments());
            }
        }
        $matchArms = $this->matchArmsFactory->createFromCondAndExprs($condAndExprs);
        $match = new Match_($condExpr, $matchArms);
        return new MatchResult($match, $shouldRemoteNextStmt);
    }
    /**
     * @param CondAndExpr[] $condAndExprs
     */
    private function resolveAssignVar(array $condAndExprs) : ?Expr
    {
        foreach ($condAndExprs as $condAndExpr) {
            $expr = $condAndExpr->getExpr();
            if (!$expr instanceof Assign) {
                continue;
            }
            return $expr->var;
        }
        return null;
    }
}
