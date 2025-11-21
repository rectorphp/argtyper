<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony62\Rector\Class_\MessageHandlerInterfaceToAttributeRector;
use Argtyper202511\Rector\Symfony\Symfony62\Rector\Class_\MessageSubscriberInterfaceToAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/pull/47068, #[AsMessageHandler] attribute
        MessageHandlerInterfaceToAttributeRector::class,
        MessageSubscriberInterfaceToAttributeRector::class,
    ]);
};
