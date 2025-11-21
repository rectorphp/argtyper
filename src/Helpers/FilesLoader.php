<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Helpers;

use Argtyper202511\Webmozart\Assert\Assert;
/**
 * @see https://jsonlines.org/
 */
final class FilesLoader
{
    /**
     * @param array<string, mixed> $record
     */
    public static function writeJsonl(string $filePath, array $record): void
    {
        // ensure file exists
        if (!file_exists($filePath)) {
            touch($filePath);
        }
        Assert::fileExists($filePath);
        $line = json_encode($record, \JSON_UNESCAPED_UNICODE) . \PHP_EOL;
        // Append the line and lock the file to prevent race conditions
        file_put_contents($filePath, $line, \FILE_APPEND | \LOCK_EX);
    }
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function loadJsonl(string $filePath): array
    {
        // ensure file exists
        if (!file_exists($filePath)) {
            touch($filePath);
        }
        Assert::fileExists($filePath);
        $lines = file($filePath, \FILE_IGNORE_NEW_LINES | \FILE_SKIP_EMPTY_LINES);
        Assert::isArray($lines);
        $records = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, \true);
            Assert::isArray($decoded);
            $records[] = $decoded;
        }
        return $records;
    }
}
