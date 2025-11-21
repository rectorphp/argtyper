<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\Orm28\Rector\MethodCall\IterateToToIterableRector;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/doctrine/orm/blob/2.8.x/UPGRADE.md#deprecated-doctrineormabstractqueryiterator
    $rectorConfig->rule(IterateToToIterableRector::class);
};
