<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector;
use Argtyper202511\Rector\DowngradePhp82\Rector\FuncCall\DowngradeIteratorCountToArrayRector;
use Argtyper202511\Rector\DowngradePhp82\Rector\FunctionLike\DowngradeStandaloneNullTrueFalseReturnTypeRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->rules([DowngradeReadonlyClassRector::class, DowngradeStandaloneNullTrueFalseReturnTypeRector::class, DowngradeIteratorCountToArrayRector::class]);
};
