<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Matcher;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
final class CallMatcher
{
    /**
     * @return FuncCall|StaticCall|MethodCall|null
     * @param \PhpParser\Node\Expr\Assign|\PhpParser\Node\Stmt\Foreach_ $node
     */
    public function matchCall($node) : ?Node
    {
        if ($node->expr instanceof MethodCall) {
            return $node->expr;
        }
        if ($node->expr instanceof StaticCall) {
            return $node->expr;
        }
        if ($node->expr instanceof FuncCall) {
            return $node->expr;
        }
        return null;
    }
}
