<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Enum;

final class ConfigFilePath
{
    public static function callLikes() : string
    {
        return \getcwd() . '/call-like-collected-data.json';
    }
    public static function funcCalls() : string
    {
        return \getcwd() . '/func-call-collected-data.json';
    }
}
