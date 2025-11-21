<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\DependencyInjection\Rector\Class_\CommandGetByTypeToConstructorInjectionRector;
use Argtyper202511\Rector\Symfony\DependencyInjection\Rector\Class_\ControllerGetByTypeToConstructorInjectionRector;
use Argtyper202511\Rector\Symfony\DependencyInjection\Rector\Class_\GetBySymfonyStringToConstructorInjectionRector;
use Argtyper202511\Rector\Symfony\DependencyInjection\Rector\Trait_\TraitGetByTypeToInjectRector;
use Argtyper202511\Rector\Symfony\Symfony28\Rector\MethodCall\GetToConstructorInjectionRector;
use Argtyper202511\Rector\Symfony\Symfony34\Rector\Closure\ContainerGetNameToTypeInTestsRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // modern step-by-step narrow approach
        ControllerGetByTypeToConstructorInjectionRector::class,
        CommandGetByTypeToConstructorInjectionRector::class,
        GetBySymfonyStringToConstructorInjectionRector::class,
        TraitGetByTypeToInjectRector::class,
        // legacy rules that require container fetch
        ContainerGetNameToTypeInTestsRector::class,
        GetToConstructorInjectionRector::class,
    ]);
};
