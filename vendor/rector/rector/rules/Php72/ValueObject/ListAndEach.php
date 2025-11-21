<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php72\ValueObject;

use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\List_;
final class ListAndEach
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\List_
     */
    private $list;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\FuncCall
     */
    private $eachFuncCall;
    public function __construct(List_ $list, FuncCall $eachFuncCall)
    {
        $this->list = $list;
        $this->eachFuncCall = $eachFuncCall;
    }
    public function getList(): List_
    {
        return $this->list;
    }
    public function getEachFuncCall(): FuncCall
    {
        return $this->eachFuncCall;
    }
}
