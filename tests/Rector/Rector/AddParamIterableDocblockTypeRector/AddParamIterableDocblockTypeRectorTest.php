<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\Rector\Rector\AddParamIterableDocblockTypeRector;

use Nette\Utils\FileSystem;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddParamIterableDocblockTypeRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        // 1. backup current phpstan dump
        $phpstanCollectedDataFilePath = getcwd() . '/' . ConfigFilePath::phpstanCollectedData();

        if (file_exists($phpstanCollectedDataFilePath)) {
            FileSystem::copy($phpstanCollectedDataFilePath, $phpstanCollectedDataFilePath . '-temp');
        }

        // 2. create temp dump

        Json::encode([

        ]);


        // 3.

        $this->doTestFile($filePath);
    }

    public static function provideData(): \Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
