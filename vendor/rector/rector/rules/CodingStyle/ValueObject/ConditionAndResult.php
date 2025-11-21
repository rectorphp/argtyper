<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\ValueObject;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class ConditionAndResult
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $conditionExpr;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $resultExpr;
    public function __construct(Expr $conditionExpr, Expr $resultExpr)
    {
        $this->conditionExpr = $conditionExpr;
        $this->resultExpr = $resultExpr;
    }
    public function getConditionExpr(): Expr
    {
        return $this->conditionExpr;
    }
    public function isIdenticalCompare(): bool
    {
        return $this->conditionExpr instanceof Identical;
    }
    public function getIdenticalVariableName(): ?string
    {
        $identical = $this->getConditionIdentical();
        if (!$identical->left instanceof Variable) {
            return null;
        }
        $variable = $identical->left;
        if ($variable->name instanceof Expr) {
            return null;
        }
        return $variable->name;
    }
    public function getResultExpr(): Expr
    {
        return $this->resultExpr;
    }
    public function getIdenticalExpr(): Expr
    {
        /** @var Identical $identical */
        $identical = $this->conditionExpr;
        return $identical->right;
    }
    private function getConditionIdentical(): Identical
    {
        Assert::isInstanceOf($this->conditionExpr, Identical::class);
        return $this->conditionExpr;
    }
}
