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
use Webmozart\Assert\Assert;

/**
 * @extends RuleTestCase<DumpMethodCallArgTypesRule>
 */
final class ResultInfererTest extends RuleTestCase
{
    public function test(): void
    {
        $collectedData = $this->collectDataInFile(__DIR__ . '/Fixture/MethodCalledArgs.php', MethodCallArgTypeCollector::class);
        $firstItem = $collectedData[0];

        $this->assertSame([
            SomeObject::class,
            'setName',
            0,
            StringType::class,
        ], $firstItem);
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

    public static function getAdditionalConfigFiles(): array
    {
        return [
            __DIR__ . '/../../config/phpstan-data-collector.neon',
        ];
    }

    /**
     * @param class-string<Collector> $collectorClass
     *
     * @return array<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function collectDataInFile(string $fixtureFilePath, string $collectorClass): array
    {
        Assert::fileExists($fixtureFilePath);

        $analyser = $this->createAnalyser();

        /** @var AnalyserResult $analyserResult */
        $analyserResult = $analyser->analyse([$fixtureFilePath], null, null, true);

        $collectedDatas = $analyserResult->getCollectedData();
        $this->assertNotEmpty($collectedDatas);

        return $collectedDatas[$fixtureFilePath][$collectorClass][0];
    }

    private function createAnalyser(): Analyser
    {
        $directRegistry = new DirectRegistry([$this->getRule()]);

        /** @var Analyser $analyser */
        $analyser = PrivatesAccessor::callMethod($this, 'getAnalyser', $directRegistry);

        return $analyser;
    }
}
