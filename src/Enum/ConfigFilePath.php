<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Enum;

final class ConfigFilePath
{
    public static function callLikes(): string
    {
        return sys_get_temp_dir() . '/argtyper-call-likes.json';
    }

    public static function funcCalls(): string
    {
        return sys_get_temp_dir() . '/argtyper-func-calls.json';
    }
}
