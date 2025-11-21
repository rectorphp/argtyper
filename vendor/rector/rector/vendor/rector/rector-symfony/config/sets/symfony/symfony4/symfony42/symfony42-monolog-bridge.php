<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\NodeAnalyzer\ArgumentAddingScope;
use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Argtyper202511\Rector\Arguments\ValueObject\ArgumentAdder;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Argtyper202511\Symfony\Bridge\Monolog\Processor\DebugProcessor', 'getLogs', 0, null, null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Argtyper202511\Symfony\Bridge\Monolog\Processor\DebugProcessor', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Argtyper202511\Symfony\Bridge\Monolog\Logger', 'getLogs', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL), new ArgumentAdder('Argtyper202511\Symfony\Bridge\Monolog\Logger', 'countErrors', 0, 'default_value', null, null, ArgumentAddingScope::SCOPE_METHOD_CALL)]);
};
