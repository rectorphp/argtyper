<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Helpers;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use TomasVotruba\SherlockTypes\ValueObject\ClassMethodType;
use Webmozart\Assert\Assert;

final class FilesLoader
{
    /**
     * @return ClassMethodType[]
     */
    public static function loadFileJson(string $filePath): array
    {
        Assert::fileExists($filePath);
        $fileContents = FileSystem::read($filePath);

        $json = Json::decode($fileContents, Json::FORCE_ARRAY);

        var_dump($json);
        die;
    }
}