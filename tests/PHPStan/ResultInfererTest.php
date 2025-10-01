<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\AnalyserResult;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\DirectRegistry;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\StringType;
use Rector\ArgTyper\Helpers\PrivatesAccessor;
use Rector\ArgTyper\PHPStan\Collectors\MethodCallArgTypeCollector;
use Rector\ArgTyper\PHPStan\Rule\DumpMethodCallArgTypesRule;
use Rector\ArgTyper\Tests\PHPStan\Source\SomeObject;

/**
 * @extends RuleTestCase<DumpMethodCallArgTypesRule>
 */
final class ResultInfererTest extends RuleTestCase
{
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../config/phpstan-data-collector.neon',
        ];
    }

    public function test(): void
    {
        $collectedData = $this->collectDataInFile(__DIR__ . '/Fixture/SomeTest.php', MethodCallArgTypeCollector::class);

        dump($collectedData);
        die;

        $this->assertSame([
            StringType::class,
            SomeObject::class,
            'getName',
        ], $collectedData);
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(DumpMethodCallArgTypesRule::class);
    }

    /**
     * @return Collector[]
     */
    protected function getCollectors(): array
    {
        return [
            self::getContainer()->getByType(MethodCallArgTypeCollector::class),
        ];
    }

    /**
     * @return array<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function collectDataInFile(string $fixtureFilePath, string $collectorClass): array
    {
        $rule = $this->getRule();

        $directRegistry = new DirectRegistry([$rule]);

        /** @var Analyser $analyser */
        $analyser = PrivatesAccessor::callMethod($this, 'getAnalyser', $directRegistry);

        /** @var AnalyserResult $analyserResult */
        $analyserResult = $analyser->analyse([$fixtureFilePath], null, null, true);

        $collectedDatas = $analyserResult->getCollectedData();

        dump($collectedDatas);
        die;

        $this->assertNotEmpty($collectedDatas);

        return $collectedDatas[$fixtureFilePath][$collectorClass];
    }
}
