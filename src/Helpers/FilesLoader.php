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

        return json_decode($fileContents, true);
    }
}
