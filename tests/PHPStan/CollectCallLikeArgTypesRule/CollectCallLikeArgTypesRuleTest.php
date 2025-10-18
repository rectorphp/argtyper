<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule;

use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\PHPStan\Rule\CollectCallLikeArgTypesRule;
use Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\Source\ObjectWithConstructor;
use Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\Source\SomeObject;
use Webmozart\Assert\Assert;

/**
 * @extends RuleTestCase<CollectCallLikeArgTypesRule>
 */
final class CollectCallLikeArgTypesRuleTest extends RuleTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // cleanup collected data file
        @unlink(ConfigFilePath::callLikes());
    }

    public function testMethodCallAndStaticCall(): void
    {
        $collectedTypes = $this->collectDataInFile(__DIR__ . '/Fixture/MethodCalledArgs.php');
        $this->assertCount(2, $collectedTypes);

        $this->assertSame([
            'class' => SomeObject::class,
            'method' => 'setName',
            'position' => 0,
            'type' => StringType::class,
        ], $collectedTypes[0]);

        $this->assertSame([
            'class' => SomeObject::class,
            'method' => 'setAge',
            'position' => 0,
            'type' => IntegerType::class,
        ], $collectedTypes[1]);
    }

    public function testConstructor(): void
    {
        $collectedType = $this->collectDataInFile(__DIR__ . '/Fixture/ConstructorArgs.php');

        $this->assertSame([
            'class' => ObjectWithConstructor::class,
            'method' => '__construct',
            'position' => 0,
            'type' => IntegerType::class,
        ], $collectedType[0]);

        $this->assertSame([
            'class' => ObjectWithConstructor::class,
            'method' => '__construct',
            'position' => 0,
            'type' => IntegerType::class,
        ], $collectedType[1]);

        $this->assertSame([
            'class' => ObjectWithConstructor::class,
            'method' => '__construct',
            'position' => 0,
            'type' => StringType::class,
        ], $collectedType[2]);
    }

    public function testFloatAsInt(): void
    {
        $collectedType = $this->collectDataInFile(__DIR__ . '/Fixture/FloatAsInt.php');

        $this->assertSame([
            'class' => ObjectWithConstructor::class,
            'method' => '__construct',
            'position' => 0,
            'type' => FloatType::class,
        ], $collectedType[0]);
        $this->assertSame([
            'class' => ObjectWithConstructor::class,
            'method' => '__construct',
            'position' => 0,
            'type' => FloatType::class,
        ], $collectedType[1]);
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
        return self::getContainer()->getByType(CollectCallLikeArgTypesRule::class);
    }

    /**
     * @return array<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function collectDataInFile(string $fixtureFilePath): array
    {
        Assert::fileExists($fixtureFilePath);

        $this->analyse([$fixtureFilePath], []);

        return FilesLoader::loadJsonl(ConfigFilePath::callLikes());
    }
}
