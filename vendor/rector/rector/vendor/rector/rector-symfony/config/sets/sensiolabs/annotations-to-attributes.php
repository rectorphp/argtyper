<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://github.com/sensiolabs/SensioFrameworkExtraBundle/pull/707
        new AnnotationToAttribute('Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache'),
        new AnnotationToAttribute('Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Entity'),
        new AnnotationToAttribute('Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted'),
        new AnnotationToAttribute('Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\ParamConverter'),
        new AnnotationToAttribute('Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Security'),
        new AnnotationToAttribute('Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Template'),
    ]);
};
