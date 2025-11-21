<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php54\Rector\Array_\LongArrayToShortArrayRector;
use Argtyper202511\Rector\Php54\Rector\Break_\RemoveZeroBreakContinueRector;
use Argtyper202511\Rector\Php54\Rector\FuncCall\RemoveReferenceFromCallRector;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([LongArrayToShortArrayRector::class, RemoveReferenceFromCallRector::class, RemoveZeroBreakContinueRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['mysqli_param_count' => 'mysqli_stmt_param_count']);
};
