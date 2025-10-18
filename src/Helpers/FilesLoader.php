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
        // create file if not existing
        if (! file_exists($filePath)) {
            touch($filePath);
        }

        $jsonContent = json_encode($data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        Assert::string($jsonContent);

        file_put_contents($filePath, $jsonContent . PHP_EOL);
    }

    /**
     * @see https://jsonlines.org/
     * @param array<string, mixed> $record
     */
    public static function writeJsonl(string $filePath, array $record): void
    {
        $line = json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // Append the line and lock the file to prevent race conditions
        file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function loadJsonl(string $filePath): array
    {
        Assert::fileExists($filePath);

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $records = [];

        foreach ($lines as $line) {
            $decoded = json_decode($line, true);
            Assert::isArray($decoded);
            $records[] = $decoded;
        }

        return $records;
    }
}
