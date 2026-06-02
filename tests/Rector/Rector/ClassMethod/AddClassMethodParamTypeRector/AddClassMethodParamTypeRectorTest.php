<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector;

use Iterator;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\ArgTyper\Configuration\CallLikeTypesConfigurationProvider;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
<<<<<<< HEAD
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\AddNullableScalarFromNullDefault;
=======
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\AddNullableForDefaultNull;
>>>>>>> ab2bbf24 (fix: keep nullability for params with default null value (fixes #9))
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\KeepDateTimeInterface;
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\KeepNullableDateTimeInterface;
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\KeepNullableScalarParam;
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector\Fixture\SkipIntToFloatOverride;
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
            new ClassMethodType(SkipIntToFloatOverride::class, 'passInteger', 0, IntegerType::class),
<<<<<<< HEAD
            new ClassMethodType(KeepNullableScalarParam::class, 'translate', 0, StringType::class),
            new ClassMethodType(AddNullableScalarFromNullDefault::class, 'translate', 0, StringType::class),
=======
            new ClassMethodType(AddNullableForDefaultNull::class, 'run', 0, StringType::class),
>>>>>>> ab2bbf24 (fix: keep nullability for params with default null value (fixes #9))
        ];
        $callLikeTypesConfigurationProvider->seedClassMethodTypes($classMethodTypes);

        $this->doTestFile($filePath);
    }

    public static function provideData(): Iterator
    {
        return self::yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/configured_rule.php';
    }
}
