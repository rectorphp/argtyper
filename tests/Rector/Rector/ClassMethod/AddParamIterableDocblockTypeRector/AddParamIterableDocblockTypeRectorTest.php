<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddParamIterableDocblockTypeRector;

use PHPUnit\Framework\Attributes\DataProvider;
use Rector\ArgTyper\Configuration\CallLikeTypesConfigurationProvider;
use Rector\ArgTyper\Rector\ValueObject\ClassMethodType;
use Rector\ArgTyper\Tests\Rector\Rector\ClassMethod\AddParamIterableDocblockTypeRector\Fixture\SomeClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AddParamIterableDocblockTypeRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        /** @var CallLikeTypesConfigurationProvider $callLikeTypesConfigurationProvider */
        $callLikeTypesConfigurationProvider = $this->getContainer()
            ->get(CallLikeTypesConfigurationProvider::class);

        $classMethodTypes = [new ClassMethodType(SomeClass::class, 'run', 0, 'array<int, string>')];
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
