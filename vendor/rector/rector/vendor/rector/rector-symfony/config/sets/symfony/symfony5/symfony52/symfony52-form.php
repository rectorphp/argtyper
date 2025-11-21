<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony52\Rector\MethodCall\FormBuilderSetDataMapperRector;
use Argtyper202511\Rector\Symfony\Symfony52\Rector\New_\PropertyPathMapperToDataMapperRector;
// https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([PropertyPathMapperToDataMapperRector::class, FormBuilderSetDataMapperRector::class]);
};
