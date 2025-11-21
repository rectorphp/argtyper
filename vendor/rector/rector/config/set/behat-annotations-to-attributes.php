<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('Given', 'Argtyper202511\\Behat\\Step\\Given', [], \true), new AnnotationToAttribute('When', 'Argtyper202511\\Behat\\Step\\When', [], \true), new AnnotationToAttribute('Then', 'Argtyper202511\\Behat\\Step\\Then', [], \true), new AnnotationToAttribute('BeforeSuite', 'Argtyper202511\\Behat\\Hook\\BeforeSuite', [], \true), new AnnotationToAttribute('AfterSuite', 'Argtyper202511\\Behat\\Hook\\AfterSuite', [], \true), new AnnotationToAttribute('BeforeFeature', 'Argtyper202511\\Behat\\Hook\\BeforeFeature', [], \true), new AnnotationToAttribute('AfterFeature', 'Argtyper202511\\Behat\\Hook\\AfterFeature', [], \true), new AnnotationToAttribute('BeforeScenario', 'Argtyper202511\\Behat\\Hook\\BeforeScenario', [], \true), new AnnotationToAttribute('AfterScenario', 'Argtyper202511\\Behat\\Hook\\AfterScenario', [], \true), new AnnotationToAttribute('BeforeStep', 'Argtyper202511\\Behat\\Hook\\BeforeStep', [], \true), new AnnotationToAttribute('AfterStep', 'Argtyper202511\\Behat\\Hook\\AfterStep', [], \true)]);
};
