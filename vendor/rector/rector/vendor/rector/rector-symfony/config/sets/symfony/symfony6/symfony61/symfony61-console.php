<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony61\Rector\Class_\CommandConfigureToAttributeRector;
use Argtyper202511\Rector\Symfony\Symfony61\Rector\Class_\CommandPropertyToAttributeRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([CommandConfigureToAttributeRector::class, CommandPropertyToAttributeRector::class]);
};
