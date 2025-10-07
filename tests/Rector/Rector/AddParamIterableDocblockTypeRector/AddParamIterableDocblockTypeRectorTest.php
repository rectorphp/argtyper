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
        $tempFilePath = ConfigFilePath::phpstanCollectedData() . '-temp';
        if (file_exists(ConfigFilePath::phpstanCollectedData())) {
            FileSystem::copy(ConfigFilePath::phpstanCollectedData(), $tempFilePath);
        }

        // 2. create temp dump

        $singleCollectedData = [
            'class' => 'Rector\ArgTyper\Tests\Rector\Rector\AddParamIterableDocblockTypeRector\Fixture\SomeClass',
            'method' => 'run',
            'position' => 0,
            'type' => 'array<int, string>',
        ];

        $collectedDataJson = Json::encode([$singleCollectedData], pretty: true);
        FileSystem::write(ConfigFilePath::phpstanCollectedData(), $collectedDataJson);

        // 2. test here
        $this->doTestFile($filePath);

        // 3. restore config
        if (file_exists($tempFilePath)) {
            FileSystem::copy($tempFilePath, ConfigFilePath::phpstanCollectedData());
            FileSystem::delete($tempFilePath);
        }
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
