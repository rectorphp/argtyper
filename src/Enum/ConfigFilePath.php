<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Enum;

final class ConfigFilePath
{
    public static function phpstanCollectedData(): string
    {
        return getcwd() . '/phpstan-collected-data.json';
    }
}
