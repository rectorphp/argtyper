<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Tests\PHPStan;

use PHPStan\Analyser\Analyser;
use PHPStan\Analyser\AnalyserResult;
use PHPStan\Collectors\CollectedData;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use PHPStan\Type\StringType;
use TomasVotruba\SherlockTypes\Helpers\PrivatesAccessor;
use TomasVotruba\SherlockTypes\PHPStan\Collectors\PHPUnitAssertMethodCallCollector;
use TomasVotruba\SherlockTypes\PHPStan\Rule\DumpPHPUnitAssertTypesRule;
use TomasVotruba\SherlockTypes\Tests\PHPStan\Source\SomeObject;

/**
 * @extends RuleTestCase<DumpPHPUnitAssertTypesRule>
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
        $collectedData = $this->collectDataInFile(__DIR__ . '/Fixture/SomeTest.php');

        $this->assertSame([
            StringType::class,
            SomeObject::class,
            'getName',
        ], $collectedData->getData());
    }

    protected function getRule(): Rule
    {
        return self::getContainer()->getByType(DumpPHPUnitAssertTypesRule::class);
    }

    /**
     * @return Collector[]
     */
    protected function getCollectors(): array
    {
        return [
            self::getContainer()->getByType(PHPUnitAssertMethodCallCollector::class),
        ];
    }

    private function collectDataInFile(string $fixtureFilePath): CollectedData
    {
        /** @var Analyser $analyser */
        $analyser = PrivatesAccessor::callMethod($this, 'getAnalyser');

        /** @var AnalyserResult $analyserResult */
        $analyserResult = $analyser->analyse([$fixtureFilePath], null, null, true);

        $collectedDatas = $analyserResult->getCollectedData();
        $this->assertNotEmpty($collectedDatas);

        return $collectedDatas[0];
    }
}