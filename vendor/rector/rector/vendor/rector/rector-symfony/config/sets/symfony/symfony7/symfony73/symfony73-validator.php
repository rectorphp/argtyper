<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony73\Rector\Class_\ConstraintOptionsToNamedArgumentsRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ConstraintOptionsToNamedArgumentsRector::class]);
};
