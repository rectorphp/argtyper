<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\AnalyserResult;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\DirectRegistry;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\ArgTyper\Helpers\PrivatesAccessor;
use Rector\ArgTyper\PHPStan\Collectors\MethodCallArgTypeCollector;
use Rector\ArgTyper\PHPStan\Collectors\StaticCallArgTypeCollector;
use Rector\ArgTyper\PHPStan\Rule\DumpMethodCallArgTypesRule;
use Rector\ArgTyper\Tests\PHPStan\Source\SomeObject;
use Webmozart\Assert\Assert;

/**
 * @extends RuleTestCase<DumpMethodCallArgTypesRule>
 */
final class ResultInfererTest extends RuleTestCase
{
    public function test(): void
    {
        $collectedData = $this->collectDataInFile(__DIR__ . '/Fixture/MethodCalledArgs.php');

        $firstItem = $collectedData[0];
        $this->assertSame([SomeObject::class, 'setName', 0, StringType::class], $firstItem);

        $secondItem = $collectedData[1];
        $this->assertSame([SomeObject::class, 'setAge', 0, IntegerType::class], $secondItem);
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../phpstan-data-collector.neon'];
    }

    protected function getRule(): Rule
    {
        return new DumpMethodCallArgTypesRule();
    }

    /**
     * @return Collector[]
     */
    protected function getCollectors(): array
    {
        return [
            self::getContainer()->getByType(MethodCallArgTypeCollector::class),
            self::getContainer()->getByType(StaticCallArgTypeCollector::class),
        ];
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
        $this->assertCount(2, $collectedDatas[$fixtureFilePath]);

        $firstCollectorData = $collectedDatas[$fixtureFilePath][MethodCallArgTypeCollector::class][0];
        $secondCollectorData = $collectedDatas[$fixtureFilePath][StaticCallArgTypeCollector::class][0];

        return array_merge($firstCollectorData, $secondCollectorData);
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
