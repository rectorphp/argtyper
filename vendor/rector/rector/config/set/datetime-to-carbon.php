<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Carbon\Rector\FuncCall\DateFuncCallToCarbonRector;
use Argtyper202511\Rector\Carbon\Rector\FuncCall\TimeFuncCallToCarbonRector;
use Argtyper202511\Rector\Carbon\Rector\MethodCall\DateTimeMethodCallToCarbonRector;
use Argtyper202511\Rector\Carbon\Rector\New_\DateTimeInstanceToCarbonRector;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([DateFuncCallToCarbonRector::class, DateTimeInstanceToCarbonRector::class, DateTimeMethodCallToCarbonRector::class, TimeFuncCallToCarbonRector::class]);
};
