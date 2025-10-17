<?php

declare(strict_types=1);

namespace PHPStan\DumpCallLikeArgTypesRule;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\AnalyserResult;
use PHPStan\Collectors\Collector;
use PHPStan\DumpCallLikeArgTypesRule\Source\ObjectWithConstructor;
use PHPStan\DumpCallLikeArgTypesRule\Source\SomeObject;
use PHPStan\Rules\DirectRegistry;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\ArgTyper\PHPStan\Collectors\CallLikeArgTypeCollector;
use Rector\ArgTyper\PHPStan\Rule\DumpCallLikeArgTypesRule;
use Webmozart\Assert\Assert;

/**
 * @extends RuleTestCase<DumpCallLikeArgTypesRule>
 */
final class DumpCallLikeArgTypesRuleTest extends RuleTestCase
{
    public function testMethodCallAndStaticCall(): void
    {
        $collectedData = $this->collectDataInFile(__DIR__ . '/Fixture/MethodCalledArgs.php');

        $firstItem = $collectedData[0][0];
        $this->assertSame([SomeObject::class, 'setName', 0, StringType::class], $firstItem);

        $secondItem = $collectedData[1][0];
        $this->assertSame([SomeObject::class, 'setAge', 0, IntegerType::class], $secondItem);
    }

    public function testConstructor(): void
    {
        $collectedData = $this->collectDataInFile(__DIR__ . '/Fixture/ConstructorArgs.php');

        $this->assertSame([ObjectWithConstructor::class, '__construct', 0, IntegerType::class], $collectedData[0][0]);
        $this->assertSame([ObjectWithConstructor::class, '__construct', 0, IntegerType::class], $collectedData[1][0]);
        $this->assertSame([ObjectWithConstructor::class, '__construct', 0, StringType::class], $collectedData[2][0]);
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
        return new DumpCallLikeArgTypesRule();
    }

    /**
     * @return Collector[]
     */
    protected function getCollectors(): array
    {
        return [self::getContainer()->getByType(CallLikeArgTypeCollector::class)];
    }

    /**
     * @return array<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function collectDataInFile(string $fixtureFilePath): array
    {
        Assert::fileExists($fixtureFilePath);

        $analyser = $this->createAnalyser();

        /** @var AnalyserResult $analyserResult */
        $analyserResult = $analyser->analyse([$fixtureFilePath], null, null, true);

        $collectedDatas = $analyserResult->getCollectedData();
        $this->assertNotEmpty($collectedDatas);

        $this->assertCount(1, $collectedDatas);
        $this->assertNotEmpty($collectedDatas[$fixtureFilePath][CallLikeArgTypeCollector::class]);

        return $collectedDatas[$fixtureFilePath][CallLikeArgTypeCollector::class];
    }

    private function createAnalyser(): Analyser
    {
        $directRegistry = new DirectRegistry([$this->getRule()]);

        /** @var Analyser $analyser */
        $analyser = $this->callPrivateMethod($this, 'getAnalyser', $directRegistry);

        return $analyser;
    }

    private function callPrivateMethod(object $object, string $methodName, mixed ...$args): mixed
    {
        $reflectionMethod = new \ReflectionMethod($object, $methodName);
        return $reflectionMethod->invoke($object, ...$args);
    }
}
