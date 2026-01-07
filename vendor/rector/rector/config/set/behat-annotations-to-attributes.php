<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Given', 'Argtyper202601\Behat\Step\Given', [], \true), new AnnotationToAttribute('When', 'Argtyper202601\Behat\Step\When', [], \true), new AnnotationToAttribute('Then', 'Argtyper202601\Behat\Step\Then', [], \true), new AnnotationToAttribute('BeforeSuite', 'Argtyper202601\Behat\Hook\BeforeSuite', [], \true), new AnnotationToAttribute('AfterSuite', 'Argtyper202601\Behat\Hook\AfterSuite', [], \true), new AnnotationToAttribute('BeforeFeature', 'Argtyper202601\Behat\Hook\BeforeFeature', [], \true), new AnnotationToAttribute('AfterFeature', 'Argtyper202601\Behat\Hook\AfterFeature', [], \true), new AnnotationToAttribute('BeforeScenario', 'Argtyper202601\Behat\Hook\BeforeScenario', [], \true), new AnnotationToAttribute('AfterScenario', 'Argtyper202601\Behat\Hook\AfterScenario', [], \true), new AnnotationToAttribute('BeforeStep', 'Argtyper202601\Behat\Hook\BeforeStep', [], \true), new AnnotationToAttribute('AfterStep', 'Argtyper202601\Behat\Hook\AfterStep', [], \true)]);
};
