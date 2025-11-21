<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Symfony\Symfony43\Rector\ClassMethod\EventDispatcherParentConstructRector;
use Argtyper202511\Rector\Symfony\Symfony43\Rector\MethodCall\MakeDispatchFirstArgumentEventRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // has lowest priority, have to be last
        'Argtyper202511\\Symfony\\Component\\EventDispatcher\\Event' => 'Argtyper202511\\Symfony\\Contracts\\EventDispatcher\\Event',
    ]);
    $rectorConfig->rules([MakeDispatchFirstArgumentEventRector::class, EventDispatcherParentConstructRector::class]);
};
