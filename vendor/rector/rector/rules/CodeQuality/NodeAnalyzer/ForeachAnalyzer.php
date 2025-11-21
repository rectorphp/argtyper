<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\Rector\PhpParser\Comparing\NodeComparator;
final class ForeachAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    public function __construct(NodeComparator $nodeComparator)
    {
        $this->nodeComparator = $nodeComparator;
    }
    /**
     * Matches$
     * foreach ($values as $value) {
     *      <$assigns[]> = $value;
     * }
     */
    public function matchAssignItemsOnlyForeachArrayVariable(Foreach_ $foreach) : ?Expr
    {
        if (\count($foreach->stmts) !== 1) {
            return null;
        }
        $onlyStatement = $foreach->stmts[0];
        if ($onlyStatement instanceof Expression) {
            $onlyStatement = $onlyStatement->expr;
        }
        if (!$onlyStatement instanceof Assign) {
            return null;
        }
        if (!$onlyStatement->var instanceof ArrayDimFetch) {
            return null;
        }
        if ($onlyStatement->var->dim instanceof Expr) {
            return null;
        }
        if (!$this->nodeComparator->areNodesEqual($foreach->valueVar, $onlyStatement->expr)) {
            return null;
        }
        return $onlyStatement->var->var;
    }
}
