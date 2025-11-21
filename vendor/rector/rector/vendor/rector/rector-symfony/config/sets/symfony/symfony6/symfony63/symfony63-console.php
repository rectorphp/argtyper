<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/commit/1650e3861b5fcd931e5d3eb1dd84bad764020d8e
        SignalableCommandInterfaceReturnTypeRector::class,
    ]);
};
