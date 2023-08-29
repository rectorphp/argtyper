<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Tests\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Analyser;
use PHPStan\Collectors\Registry;
use PHPStan\DependencyInjection\ContainerFactory;
use PHPStan\Node\CollectedDataNode;
use TomasVotruba\SherlockTypes\Tests\AbstractTestCase;
use Tracy\Dumper;

final class ResultInfererTest extends AbstractTestCase
{
    // run PHPStan on test case file and extract types
    public function test(): void
    {
        $fixtureFilePath = __DIR__ . '/Fixture/SomeTest.php';

        $phpstanContainerFactory = new ContainerFactory(getcwd());

        $phpstanContainer = $phpstanContainerFactory->create(
            tempDirectory: sys_get_temp_dir() . '/shelock-types-phpstan-5',
            additionalConfigFiles: [__DIR__ . '/../../config/phpstan-data-collector.neon'],
            analysedPaths: [__DIR__ . '/Fixture'],
        );

        /** @var Analyser $analyser */
        $analyser = $phpstanContainer->getByType(Analyser::class);
        $analyserResult = $analyser->analyse([$fixtureFilePath], null, null, true);

        /** @var Registry $collectorRegistry */
        $collectorRegistry = $phpstanContainer->getByType(Registry::class);
        dump($collectorRegistry->getCollectors(MethodCall::class));

        /** @var \PHPStan\Rules\Registry $ruleRegistry */
        $ruleRegistry = $phpstanContainer->getByType(\PHPStan\Rules\Registry::class);
        Dumper::dump($ruleRegistry->getRules(CollectedDataNode::class), [
            'depth' => 2,
        ]);

        dump($analyserResult->getCollectedData());
        die();

        // create PHSPtan here too :)
        // get file analyser
        // run rule and ocllector
        // compare expected json
    }
}