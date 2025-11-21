<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony51\Rector\Class_\LogoutHandlerToLogoutEventSubscriberRector;
use Argtyper202511\Rector\Symfony\Symfony51\Rector\Class_\LogoutSuccessHandlerToLogoutEventSubscriberRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/pull/36243
        LogoutHandlerToLogoutEventSubscriberRector::class,
        LogoutSuccessHandlerToLogoutEventSubscriberRector::class,
    ]);
};
