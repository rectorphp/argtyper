<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\EventDispatcher\LegacyEventDispatcherProxy' => 'Argtyper202511\Symfony\Component\EventDispatcher\EventDispatcherInterface']);
};
