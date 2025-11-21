<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Argtyper202511\Rector\Privatization\Rector\MethodCall\PrivatizeLocalGetterToPropertyRector;
use Argtyper202511\Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([PrivatizeLocalGetterToPropertyRector::class, PrivatizeFinalClassPropertyRector::class, PrivatizeFinalClassMethodRector::class]);
};
