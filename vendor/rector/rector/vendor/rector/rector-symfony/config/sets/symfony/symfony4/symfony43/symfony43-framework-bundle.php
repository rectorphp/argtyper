<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Symfony\Symfony43\Rector\MethodCall\ConvertRenderTemplateShortNotationToBundleSyntaxRector;
use Argtyper202511\Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertIsSuccessfulRector;
use Argtyper202511\Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertResponseCodeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // assets deprecation
        'Argtyper202511\\Symfony\\Bundle\\FrameworkBundle\\Templating\\Helper\\AssetsHelper' => 'Argtyper202511\\Symfony\\Component\\Asset\\Packages',
        // templating
        'Argtyper202511\\Symfony\\Bundle\\FrameworkBundle\\Templating\\EngineInterface' => 'Argtyper202511\\Symfony\\Component\\Templating\\EngineInterface',
    ]);
    $rectorConfig->rules([
        ConvertRenderTemplateShortNotationToBundleSyntaxRector::class,
        # https://symfony.com/blog/new-in-symfony-4-3-better-test-assertions
        //
        WebTestCaseAssertIsSuccessfulRector::class,
        WebTestCaseAssertResponseCodeRector::class,
    ]);
};
