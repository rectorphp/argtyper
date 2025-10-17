<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\DumpFuncCallArgTypesRule;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\AnalyserResult;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\DirectRegistry;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\StringType;
use Rector\ArgTyper\PHPStan\Collectors\FuncCallTypeCollector;
use Rector\ArgTyper\PHPStan\Rule\DumpFuncCallArgTypesRule;
use Rector\ArgTyper\Tests\PHPStan\DumpCallLikeArgTypesRule\Source\SomeObject;
use Webmozart\Assert\Assert;

/**
 * @extends RuleTestCase<DumpFuncCallArgTypesRule>
 */
final class DumpFuncCallArgTypesRuleTest extends RuleTestCase
{
    public function testMethodCallAndStaticCall(): void
    {
        $collectedData = $this->collectDataInFile(__DIR__ . '/Fixture/SimpleFunctionCall.php');

        dump($collectedData);
        die;

        $firstItem = $collectedData[0][0];
        $this->assertSame([SomeObject::class, 'setName', 0, StringType::class], $firstItem);
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
        return new DumpFuncCallArgTypesRule();
    }

    /**
     * @return Collector[]
     */
    protected function getCollectors(): array
    {
        return [self::getContainer()->getByType(FuncCallTypeCollector::class)];
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
        $this->assertNotEmpty($collectedDatas[$fixtureFilePath][FuncCallTypeCollector::class]);

        return $collectedDatas[$fixtureFilePath][FuncCallTypeCollector::class];
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
