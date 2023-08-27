<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes;

use Nette\Utils\FileSystem;
use Webmozart\Assert\Assert;

final class TemplatePrinter
{
    /**
     * @param array<string, mixed> $variables
     */
    public static function print(string $templateFilePath, array $variables): string
    {
        Assert::fileExists($templateFilePath);

        $templateContents = FileSystem::read($templateFilePath);

        return strtr($templateContents, $variables);
    }
}