<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony34\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use Argtyper202511\Rector\Symfony\Symfony34\Rector\ClassMethod\RemoveServiceFromSensioRouteRector;
use Argtyper202511\Rector\Symfony\Symfony34\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([MergeMethodAnnotationToRouteAnnotationRector::class, RemoveServiceFromSensioRouteRector::class, ReplaceSensioRouteAnnotationWithSymfonyRector::class]);
};
