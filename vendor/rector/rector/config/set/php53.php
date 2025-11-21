<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php53\Rector\FuncCall\DirNameFileConstantToDirConstantRector;
use Argtyper202511\Rector\Php53\Rector\Ternary\TernaryToElvisRector;
use Argtyper202511\Rector\Php53\Rector\Variable\ReplaceHttpServerVarsByServerRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([TernaryToElvisRector::class, DirNameFileConstantToDirConstantRector::class, ReplaceHttpServerVarsByServerRector::class]);
};
