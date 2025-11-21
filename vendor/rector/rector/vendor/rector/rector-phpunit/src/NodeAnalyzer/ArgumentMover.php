<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
final class ArgumentMover
{
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    public function removeFirstArg($node): void
    {
        if ($node->isFirstClassCallable()) {
            return;
        }
        $methodArguments = $node->getArgs();
        array_shift($methodArguments);
        $node->args = $methodArguments;
    }
}
