<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use Webmozart\Assert\Assert;

final class FilesLoader
{
    /**
     * @return array<string, mixed>
     */
    public static function loadFileJson(string $filePath): array
    {
        Assert::fileExists($filePath);
        $fileContents = FileSystem::read($filePath);

        return Json::decode($fileContents, Json::FORCE_ARRAY);
    }
}
