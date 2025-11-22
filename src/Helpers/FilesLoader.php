<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

// no extrnal services are allowed here, as this is used in PHPStan with project autoload, not local one
// only PHPStan, PhpParser and Rector\ArgTyper is allowed here (including transitional dependencies)

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
        if (! file_exists($filePath)) {
            touch($filePath);
        }

        $line = json_encode($record, JSON_UNESCAPED_UNICODE) . PHP_EOL;

        // Append the line and lock the file to prevent race conditions
        file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function loadJsonl(string $filePath): array
    {
        // ensure file exists
        if (! file_exists($filePath)) {
            touch($filePath);
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! is_array($lines)) {
            throw new \RuntimeException(sprintf('Could not read file "%s".', $filePath));
        }

        $records = [];
        foreach ($lines as $line) {
            $decoded = json_decode($line, true);

            if (! is_array($decoded)) {
                throw new \RuntimeException(sprintf('Could not decode line "%s" in file "%s".', $line, $filePath));
            }

            $records[] = $decoded;
        }

        return $records;
    }
}
