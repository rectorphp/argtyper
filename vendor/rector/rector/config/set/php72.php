<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php72\Rector\Assign\ListEachRector;
use Argtyper202511\Rector\Php72\Rector\Assign\ReplaceEachAssignmentWithKeyCurrentRector;
use Argtyper202511\Rector\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector;
use Argtyper202511\Rector\Php72\Rector\FuncCall\GetClassOnNullRector;
use Argtyper202511\Rector\Php72\Rector\FuncCall\ParseStrWithResultArgumentRector;
use Argtyper202511\Rector\Php72\Rector\FuncCall\StringifyDefineRector;
use Argtyper202511\Rector\Php72\Rector\FuncCall\StringsAssertNakedRector;
use Argtyper202511\Rector\Php72\Rector\Unset_\UnsetCastRector;
use Argtyper202511\Rector\Php72\Rector\While_\WhileEachToForeachRector;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        # and imagewbmp
        'jpeg2wbmp' => 'imagecreatefromjpeg',
        # or imagewbmp
        'png2wbmp' => 'imagecreatefrompng',
        #migration72.deprecated.gmp_random-function
        # http://php.net/manual/en/migration72.deprecated.php
        # or gmp_random_range
        'gmp_random' => 'gmp_random_bits',
        'read_exif_data' => 'exif_read_data',
    ]);
    $rectorConfig->rules([GetClassOnNullRector::class, ParseStrWithResultArgumentRector::class, StringsAssertNakedRector::class, CreateFunctionToAnonymousFunctionRector::class, StringifyDefineRector::class, WhileEachToForeachRector::class, ListEachRector::class, ReplaceEachAssignmentWithKeyCurrentRector::class, UnsetCastRector::class]);
};
