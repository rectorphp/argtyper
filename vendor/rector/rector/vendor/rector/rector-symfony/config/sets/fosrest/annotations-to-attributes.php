<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AnnotationToAttributeRector::class, [
        // @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/2325
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Copy'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Delete'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Get'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Head'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Link'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Lock'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Mkcol'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Move'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Options'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Patch'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Post'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\PropFind'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\PropPatch'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Put'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Route'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Unlink'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\Unlock'),
        // @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/2326
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\View'),
        // @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/2327
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\FileParam'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\QueryParam'),
        new AnnotationToAttribute('Argtyper202511\\FOS\\RestBundle\\Controller\\Annotations\\RequestParam'),
    ]);
};
