<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\ValueObject;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
final class FuncCallAndExpr
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\FuncCall
     */
    private $funcCall;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    public function __construct(FuncCall $funcCall, Expr $expr)
    {
        $this->funcCall = $funcCall;
        $this->expr = $expr;
    }
    public function getFuncCall(): FuncCall
    {
        return $this->funcCall;
    }
    public function getExpr(): Expr
    {
        return $this->expr;
    }
}
