<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Rector;

use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Param;
final class NodeTypeChecker
{
    public static function isParamNullable(Param $param) : bool
    {
        if ($param->type instanceof NullableType) {
            return \true;
        }
        if (!$param->default instanceof ConstFetch) {
            return \false;
        }
        $constFetch = $param->default;
        $constantName = $constFetch->name->toLowerString();
        return $constantName === 'null';
    }
}
