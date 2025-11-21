<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\CollectFuncCallArgTypesRule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\PHPStan\Rule\CollectFuncCallArgTypesRule;
use Webmozart\Assert\Assert;

/**
 * @extends RuleTestCase<CollectFuncCallArgTypesRule>
 */
final class CollectFuncCallArgTypesRuleTest extends RuleTestCase
{
    public function test(): void
    {
        $collectedTypes = $this->collectDataInFile(__DIR__ . '/Fixture/SimpleFunctionCall.php');

        $this->assertSame([
            'function' => 'Rector\ArgTyper\Tests\PHPStan\CollectFuncCallArgTypesRule\Source\someFunction',
            'position' => 0,
            'type' => IntegerType::class,
        ], $collectedTypes[0]);

        $this->assertSame([
            'function' => 'Rector\ArgTyper\Tests\PHPStan\CollectFuncCallArgTypesRule\Source\someFunction',
            'position' => 1,
            'type' => FloatType::class,
        ], $collectedTypes[1]);
    }

    /**
     * @return string[]
     */
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../../phpstan-data-collector.neon'];
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(CollectFuncCallArgTypesRule::class);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function collectDataInFile(string $fixtureFilePath): array
    {
        Assert::fileExists($fixtureFilePath);
        $this->analyse([$fixtureFilePath], []);

        return FilesLoader::loadJsonl(ConfigFilePath::funcCalls());
    }
}
