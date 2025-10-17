<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\Rector\Rector\AddParamTypeRector;

use PHPStan\Type\IntegerType;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddParamTypeRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        // 1. backup current phpstan dump
        $tempFilePath = ConfigFilePath::callLikes() . '-temp';
        if (file_exists(ConfigFilePath::callLikes())) {
            copy(ConfigFilePath::callLikes(), $tempFilePath);
        }

        // 2. create temp dump

        $collectedData = [
            [
                'class' => 'Rector\ArgTyper\Tests\Rector\Rector\AddParamTypeRector\Fixture\SkipParentContract',
                'method' => 'checkItem',
                'position' => 0,
                'type' => IntegerType::class,
            ],
            [
                'class' => 'Rector\ArgTyper\Tests\Rector\Rector\AddParamTypeRector\Fixture\KeepNullableDateTimeInterface',
                'method' => 'record',
                'position' => 0,
                'type' => 'object:' . \DateTime::class,
            ],
            [
                'class' => 'Rector\ArgTyper\Tests\Rector\Rector\AddParamTypeRector\Fixture\KeepDateTimeInterface',
                'method' => 'record',
                'position' => 0,
                'type' => 'object:' . \DateTime::class,
            ],
        ];

        $collectedDataJson = json_encode($collectedData, JSON_PRETTY_PRINT);
        file_put_contents(ConfigFilePath::callLikes(), $collectedDataJson);

        // 2. test here
        $this->doTestFile($filePath);

        // 3. restore config
        if (file_exists($tempFilePath)) {
            copy($tempFilePath, ConfigFilePath::callLikes());
            unlink($tempFilePath);
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
