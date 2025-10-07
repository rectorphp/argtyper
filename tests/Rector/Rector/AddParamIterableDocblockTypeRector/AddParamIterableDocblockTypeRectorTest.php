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
        if (file_exists(ConfigFilePath::phpstanCollectedData())) {
            FileSystem::copy(ConfigFilePath::phpstanCollectedData(), ConfigFilePath::phpstanCollectedData() . '-temp');
        }

        // 2. create temp dump

        $singleCollectedData = [
            'class' => \Rector\ArgTyper\Tests\Rector\Rector\AddParamIterableDocblockTypeRector\Fixture\SomeClass::class,
            'method' => 'run',
            'position' => 0,
            'type' => 'array<int, string>'
        ];

        $collectedDataJson = Json::encode([$singleCollectedData], Json::PRETTY);
        FileSystem::write($collectedDataJson, ConfigFilePath::phpstanCollectedData());

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
