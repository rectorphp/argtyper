<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use Webmozart\Assert\Assert;

final class FilesLoader
{
    /**
     * @return array<string, mixed>
     */
    public static function loadFileJson(string $filePath): array
    {
        Assert::fileExists($filePath);
        $fileContents = file_get_contents($filePath);

        Assert::string($fileContents);
        return json_decode($fileContents, true);
    }

    /**
     * @param array<mixed, mixed> $data
     */
    public static function dumpJsonToFile(string $filePath, array $data): void
    {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        Assert::string($jsonContent);

        file_put_contents($filePath, $jsonContent);
    }
}
