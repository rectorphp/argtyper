<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/44271
        'Argtyper202511\Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransportFactory' => 'Argtyper202511\Symfony\Component\Notifier\Bridge\Vonage\VonageTransportFactory',
        'Argtyper202511\Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransport' => 'Argtyper202511\Symfony\Component\Notifier\Bridge\Vonage\VonageTransport',
    ]);
};
