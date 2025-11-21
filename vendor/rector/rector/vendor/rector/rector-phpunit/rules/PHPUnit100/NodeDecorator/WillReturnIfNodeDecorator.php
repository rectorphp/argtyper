<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\PHPUnit100\NodeDecorator;

use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
final class WillReturnIfNodeDecorator
{
    public function decorate(Closure $callbackClosure, ?MethodCall $willReturnOnConsecutiveMethodCall) : void
    {
        if (!$willReturnOnConsecutiveMethodCall instanceof MethodCall) {
            return;
        }
        foreach ($callbackClosure->stmts as $key => $stmt) {
            if (!$stmt instanceof If_) {
                continue;
            }
            $currentArg = $willReturnOnConsecutiveMethodCall->getArgs()[$key];
            $stmt->stmts[] = new Return_($currentArg->value);
        }
    }
}
