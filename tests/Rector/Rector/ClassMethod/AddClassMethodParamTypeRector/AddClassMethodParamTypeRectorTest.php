<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector;

use PHPStan\Type\IntegerType;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\ArgTyper\Configuration\CallLikeTypesConfigurationProvider;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\KeepDateTimeInterface;
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\KeepNullableDateTimeInterface;
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\SkipParentContract;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\ArgTyper\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector
 */
final class AddClassMethodParamTypeRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        /** @var CallLikeTypesConfigurationProvider $callLikeTypesConfigurationProvider */
        $callLikeTypesConfigurationProvider = $this->getContainer()
            ->get(CallLikeTypesConfigurationProvider::class);

        $classMethodTypes = [
            new ClassMethodType(SkipParentContract::class, 'checkItem', 0, IntegerType::class),
            new ClassMethodType(KeepNullableDateTimeInterface::class, 'record', 0, 'object:' . \DateTime::class),
            new ClassMethodType(KeepDateTimeInterface::class, 'record', 0, 'object:' . \DateTime::class),
        ];
        $callLikeTypesConfigurationProvider->seedClassMethodTypes($classMethodTypes);

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
