<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\Bundle210\Rector\Class_\EventSubscriberInterfaceToAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    // @see https://github.com/doctrine/DoctrineBundle/pull/1592
    $rectorConfig->rule(EventSubscriberInterfaceToAttributeRector::class);
};
