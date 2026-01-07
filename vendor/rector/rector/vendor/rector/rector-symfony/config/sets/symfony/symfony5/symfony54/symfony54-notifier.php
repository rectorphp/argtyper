<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/44271
        'Argtyper202601\Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransportFactory' => 'Argtyper202601\Symfony\Component\Notifier\Bridge\Vonage\VonageTransportFactory',
        'Argtyper202601\Symfony\Component\Notifier\Bridge\Nexmo\NexmoTransport' => 'Argtyper202601\Symfony\Component\Notifier\Bridge\Vonage\VonageTransport',
    ]);
};
