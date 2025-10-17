<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector;

use PHPStan\Type\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use Rector\ArgTyper\Configuration\FuncCallTypesConfigurationProvider;
use Rector\ArgTyper\Rector\ValueObject\FuncCallType;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

/**
 * @see \Rector\ArgTyper\Rector\Rector\Function_\AddFunctionParamTypeRector
 */
final class AddFunctionParamTypeRectorTest extends AbstractRectorTestCase
{
    #[DataProvider('provideData')]
    public function test(string $filePath): void
    {
        /** @var FuncCallTypesConfigurationProvider $funcCallTypesConfigurationProvider */
        $funcCallTypesConfigurationProvider = $this->getContainer()
            ->get(FuncCallTypesConfigurationProvider::class);

        $funcCallTypesConfigurationProvider->seedTypes([
            new FuncCallType(
                'Rector\ArgTyper\Tests\Rector\Rector\Function_\AddFunctionParamTypeRector\Fixture\simpleFunction',
                0,
                StringType::class
            ),
        ]);

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
