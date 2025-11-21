<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php71\Rector\Assign\AssignArrayToStringRector;
use Argtyper202511\Rector\Php71\Rector\BinaryOp\BinaryOpBetweenNumberAndStringRector;
use Argtyper202511\Rector\Php71\Rector\BooleanOr\IsIterableRector;
use Argtyper202511\Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Argtyper202511\Rector\Php71\Rector\List_\ListToArrayDestructRector;
use Argtyper202511\Rector\Php71\Rector\TryCatch\MultiExceptionCatchRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([IsIterableRector::class, MultiExceptionCatchRector::class, AssignArrayToStringRector::class, RemoveExtraParametersRector::class, BinaryOpBetweenNumberAndStringRector::class, ListToArrayDestructRector::class]);
};
