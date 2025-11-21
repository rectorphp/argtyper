<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php82\Rector\Class_\ReadOnlyClassRector;
use Argtyper202511\Rector\Php82\Rector\Encapsed\VariableInStringInterpolationFixerRector;
use Argtyper202511\Rector\Php82\Rector\FuncCall\Utf8DecodeEncodeToMbConvertEncodingRector;
use Argtyper202511\Rector\Php82\Rector\New_\FilesystemIteratorSkipDotsRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ReadOnlyClassRector::class, Utf8DecodeEncodeToMbConvertEncodingRector::class, FilesystemIteratorSkipDotsRector::class, VariableInStringInterpolationFixerRector::class]);
};
