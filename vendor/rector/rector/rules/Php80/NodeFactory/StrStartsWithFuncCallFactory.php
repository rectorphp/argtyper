<?php

declare (strict_types=1);
namespace Rector\Php80\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Name;
use Rector\Php80\ValueObject\StrStartsWith;
final class StrStartsWithFuncCallFactory
{
    /**
     * @return \PhpParser\Node\Expr\FuncCall|\PhpParser\Node\Expr\BooleanNot
     */
    public function createStrStartsWith(StrStartsWith $strStartsWith)
    {
        $args = [new Arg($strStartsWith->getHaystackExpr()), new Arg($strStartsWith->getNeedleExpr())];
        $funcCall = new FuncCall(new Name('str_starts_with'), $args);
        if ($strStartsWith->isPositive()) {
            return $funcCall;
        }
        return new BooleanNot($funcCall);
    }
}
