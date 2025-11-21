<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\CodeQuality\ValueObject\KeyAndExpr;
use Argtyper202511\Rector\Exception\NotImplementedYetException;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
final class VariableDimFetchAssignResolver
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ExprAnalyzer $exprAnalyzer, ValueResolver $valueResolver)
    {
        $this->exprAnalyzer = $exprAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    /**
     * @param Stmt[] $stmts
     * @return array<mixed, KeyAndExpr[]>
     */
    public function resolveFromStmtsAndVariable(array $stmts, ?Assign $emptyArrayAssign) : array
    {
        $exprs = [];
        $key = 0;
        foreach ($stmts as $stmt) {
            if ($stmt instanceof Expression && $stmt->expr === $emptyArrayAssign) {
                continue;
            }
            if ($stmt instanceof Return_) {
                continue;
            }
            if (!$stmt instanceof Expression) {
                return [];
            }
            $stmtExpr = $stmt->expr;
            if (!$stmtExpr instanceof Assign) {
                return [];
            }
            $assign = $stmtExpr;
            $dimValues = [];
            $arrayDimFetch = $assign->var;
            while ($arrayDimFetch instanceof ArrayDimFetch) {
                if ($arrayDimFetch->dim instanceof Expr && $this->exprAnalyzer->isDynamicExpr($arrayDimFetch->dim)) {
                    return [];
                }
                $dimValues[] = $arrayDimFetch->dim instanceof Expr ? $this->valueResolver->getValue($arrayDimFetch->dim) : $key;
                $arrayDimFetch = $arrayDimFetch->var;
            }
            ++$key;
            $this->setNestedKeysExpr($exprs, $dimValues, $assign->expr);
        }
        return $exprs;
    }
    /**
     * @param mixed[] $exprsByKeys
     * @param array<string|int> $keys
     */
    private function setNestedKeysExpr(array &$exprsByKeys, array $keys, Expr $expr) : void
    {
        $reference =& $exprsByKeys;
        $keys = \array_reverse($keys);
        foreach ($keys as $key) {
            if ($reference instanceof Array_) {
                // currently it fails here with Cannot use object of type PhpParser\Node\Expr\Array_ as array
                throw new NotImplementedYetException();
            }
            $reference =& $reference[$key];
        }
        $reference = $expr;
    }
}
