<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddFunctionParamTypeRector;

use PhpCsFixer\Config;
use PHPStan\Type\IntegerType;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddFunctionParamTypeRectorTest extends AbstractRectorTestCase
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
                'class' => 'Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddParamTypeRector\Fixture\SkipParentContract',
                'method' => 'checkItem',
                'position' => 0,
                'type' => IntegerType::class,
            ],
            [
                'class' => 'Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddParamTypeRector\Fixture\KeepNullableDateTimeInterface',
                'method' => 'record',
                'position' => 0,
                'type' => 'object:' . \DateTime::class,
            ],
            [
                'class' => 'Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddParamTypeRector\Fixture\KeepDateTimeInterface',
                'method' => 'record',
                'position' => 0,
                'type' => 'object:' . \DateTime::class,
            ],
        ];

        // @todo improt this one to decoupel from filesytem, use in-memory structure, e.g. set to config provider service
        FilesLoader::dumpJsonToFile(ConfigFilePath::funcCalls(), $collectedData);

        // 2. test here
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
