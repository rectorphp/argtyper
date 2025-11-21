<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\EarlyReturn\NodeTransformer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\Rector\NodeManipulator\BinaryOpManipulator;
final class ConditionInverter
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function createInvertedCondition(Expr $expr) : Expr
    {
        // inverse condition
        if ($expr instanceof BinaryOp) {
            $binaryOp = $this->binaryOpManipulator->invertCondition($expr);
            if (!$binaryOp instanceof BinaryOp) {
                return new BooleanNot($expr);
            }
            if ($binaryOp instanceof BooleanAnd) {
                return new BooleanNot($expr);
            }
            return $binaryOp;
        }
        if ($expr instanceof BooleanNot) {
            return $expr->expr;
        }
        return new BooleanNot($expr);
    }
}
