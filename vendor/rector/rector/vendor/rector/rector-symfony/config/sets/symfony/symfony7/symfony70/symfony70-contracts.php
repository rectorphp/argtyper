<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    // the "@required" was dropped, use attribute instead
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [new AnnotationToAttribute('required', 'Argtyper202511\\Symfony\\Contracts\\Service\\Attribute\\Required')]);
};
