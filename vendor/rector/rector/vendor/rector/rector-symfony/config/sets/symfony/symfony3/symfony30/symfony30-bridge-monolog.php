<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Bridge\Monolog\Logger' => 'Argtyper202511\Psr\Log\LoggerInterface']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Bridge\Monolog\Logger', 'emerg', 'emergency'), new MethodCallRename('Argtyper202511\Symfony\Bridge\Monolog\Logger', 'crit', 'critical'), new MethodCallRename('Argtyper202511\Symfony\Bridge\Monolog\Logger', 'err', 'error'), new MethodCallRename('Argtyper202511\Symfony\Bridge\Monolog\Logger', 'warn', 'warning')]);
};
