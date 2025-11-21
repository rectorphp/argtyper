<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Argtyper202511\Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Argtyper202511\Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Argtyper202511\Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Argtyper202511\Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Argtyper202511\Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Argtyper202511\Rector\EarlyReturn\Rector\Return_\ReturnBinaryOrToEarlyReturnRector;
use Argtyper202511\Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ChangeNestedForeachIfsToEarlyContinueRector::class, ChangeIfElseValueAssignToEarlyReturnRector::class, ChangeNestedIfsToEarlyReturnRector::class, RemoveAlwaysElseRector::class, ChangeOrIfContinueToMultiContinueRector::class, PreparedValueToEarlyReturnRector::class, ReturnBinaryOrToEarlyReturnRector::class, ReturnEarlyIfVariableRector::class]);
};
