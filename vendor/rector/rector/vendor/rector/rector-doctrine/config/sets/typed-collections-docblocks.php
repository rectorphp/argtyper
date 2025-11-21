<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_\CompletePropertyDocblockFromToManyRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionDocblockGenericTypeRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\DefaultCollectionKeyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // safe rules that handle only docblocks
        CollectionDocblockGenericTypeRector::class,
        DefaultCollectionKeyRector::class,
        CompletePropertyDocblockFromToManyRector::class,
    ]);
};
