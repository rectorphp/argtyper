<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
final class FormOptionsArrayMatcher
{
    public function match(MethodCall $methodCall) : ?Array_
    {
        if (!isset($methodCall->getArgs()[2])) {
            return null;
        }
        $optionsArray = $methodCall->getArgs()[2]->value;
        if (!$optionsArray instanceof Array_) {
            return null;
        }
        return $optionsArray;
    }
}
