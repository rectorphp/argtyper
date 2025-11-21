<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // swift mailer
        'Argtyper202511\Symfony\Bridge\Swiftmailer\DataCollector\MessageDataCollector' => 'Argtyper202511\Symfony\Bundle\SwiftmailerBundle\DataCollector\MessageDataCollector',
    ]);
};
