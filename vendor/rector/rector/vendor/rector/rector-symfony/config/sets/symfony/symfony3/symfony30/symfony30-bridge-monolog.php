<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\\Symfony\\Bridge\\Monolog\\Logger' => 'Argtyper202511\\Psr\\Log\\LoggerInterface']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\\Symfony\\Bridge\\Monolog\\Logger', 'emerg', 'emergency'), new MethodCallRename('Argtyper202511\\Symfony\\Bridge\\Monolog\\Logger', 'crit', 'critical'), new MethodCallRename('Argtyper202511\\Symfony\\Bridge\\Monolog\\Logger', 'err', 'error'), new MethodCallRename('Argtyper202511\\Symfony\\Bridge\\Monolog\\Logger', 'warn', 'warning')]);
};
