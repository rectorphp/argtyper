<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Tests\PHPStan;

use PHPStan\Analyser\AnalyserResult;
use PHPStan\Collectors\Collector;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;
use TomasVotruba\SherlockTypes\Helpers\PrivatesAccessor;
use TomasVotruba\SherlockTypes\PHPStan\Collectors\PHPUnitAssertMethodCallCollector;
use TomasVotruba\SherlockTypes\PHPStan\Rule\DumpPHPUnitAssertTypesRule;

final class ResultInfererTest extends RuleTestCase
{
    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../config/phpstan-data-collector.neon'
        ];
    }


    // run PHPStan on test case file and extract types
    public function test(): void
    {
        $fixtureFilePath = __DIR__ . '/Fixture/SomeTest.php';

        // get analyser

        $analyser =PrivatesAccessor::callMethod($this, 'getAnalyser');

        /** @var AnalyserResult $analyserResult */
        $analyserResult = $analyser->analyse([$fixtureFilePath], null, null, true);


        $collectedData = $analyserResult->getCollectedData();

        d($analyserResult->getCollectedData()[0]->getData());

        $this->assertNotEmpty($collectedData);

        // create PHSPtan here too :)
        // get file analyser
        // run rule and ocllector
        // compare expected json
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
}