<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeManipulator;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Finally_;
use Argtyper202511\PhpParser\Node\Stmt\TryCatch;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\PhpParser\Comparing\NodeComparator;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class StmtsManipulator
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Comparing\NodeComparator
     */
    private $nodeComparator;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\ExprUsedInNodeAnalyzer
     */
    private $exprUsedInNodeAnalyzer;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, BetterNodeFinder $betterNodeFinder, NodeComparator $nodeComparator, ExprUsedInNodeAnalyzer $exprUsedInNodeAnalyzer)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeComparator = $nodeComparator;
        $this->exprUsedInNodeAnalyzer = $exprUsedInNodeAnalyzer;
    }
    /**
     * @param Stmt[] $stmts
     * @return null|\PhpParser\Node\Expr|\PhpParser\Node\Stmt
     */
    public function getUnwrappedLastStmt(array $stmts)
    {
        if ($stmts === []) {
            return null;
        }
        \end($stmts);
        $lastStmtKey = \key($stmts);
        \reset($stmts);
        $lastStmt = $stmts[$lastStmtKey];
        if ($lastStmt instanceof Expression) {
            $lastStmt->expr->setAttribute(AttributeKey::COMMENTS, $lastStmt->getAttribute(AttributeKey::COMMENTS));
            return $lastStmt->expr;
        }
        return $lastStmt;
    }
    /**
     * @param Stmt[] $stmts
     * @return Stmt[]
     */
    public function filterOutExistingStmts(ClassMethod $classMethod, array $stmts) : array
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use(&$stmts) {
            foreach ($stmts as $key => $assign) {
                if (!$this->nodeComparator->areNodesEqual($node, $assign)) {
                    continue;
                }
                unset($stmts[$key]);
            }
            return null;
        });
        return $stmts;
    }
    public function isVariableUsedInNextStmt(StmtsAwareInterface $stmtsAware, int $jumpToKey, string $variableName) : bool
    {
        if ($stmtsAware->stmts === null) {
            return \false;
        }
        \end($stmtsAware->stmts);
        $lastKey = \key($stmtsAware->stmts);
        \reset($stmtsAware->stmts);
        $stmts = [];
        for ($key = $jumpToKey; $key <= $lastKey; ++$key) {
            if (!isset($stmtsAware->stmts[$key])) {
                // can be just removed
                continue;
            }
            $stmts[] = $stmtsAware->stmts[$key];
        }
        if ($stmtsAware instanceof TryCatch) {
            $stmts = \array_merge($stmts, $stmtsAware->catches);
            if ($stmtsAware->finally instanceof Finally_) {
                $stmts = \array_merge($stmts, $stmtsAware->finally->stmts);
            }
        }
        $variable = new Variable($variableName);
        return (bool) $this->betterNodeFinder->findFirst($stmts, function (Node $subNode) use($variable) : bool {
            return $this->exprUsedInNodeAnalyzer->isUsed($subNode, $variable);
        });
    }
}
