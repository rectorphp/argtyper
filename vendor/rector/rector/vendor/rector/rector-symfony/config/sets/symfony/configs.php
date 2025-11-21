<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Configs\Rector\Closure\FromServicePublicToDefaultsPublicRector;
use Argtyper202511\Rector\Symfony\Configs\Rector\Closure\MergeServiceNameTypeRector;
use Argtyper202511\Rector\Symfony\Configs\Rector\Closure\RemoveConstructorAutowireServiceRector;
use Argtyper202511\Rector\Symfony\Configs\Rector\Closure\ServiceArgsToServiceNamedArgRector;
use Argtyper202511\Rector\Symfony\Configs\Rector\Closure\ServiceSetStringNameToClassNameRector;
use Argtyper202511\Rector\Symfony\Configs\Rector\Closure\ServiceSettersToSettersAutodiscoveryRector;
use Argtyper202511\Rector\Symfony\Configs\Rector\Closure\ServiceTagsToDefaultsAutoconfigureRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([MergeServiceNameTypeRector::class, ServiceArgsToServiceNamedArgRector::class, ServiceSetStringNameToClassNameRector::class, ServiceSettersToSettersAutodiscoveryRector::class, ServiceTagsToDefaultsAutoconfigureRector::class, RemoveConstructorAutowireServiceRector::class, FromServicePublicToDefaultsPublicRector::class]);
};
