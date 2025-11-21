<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\DowngradePhp83\Rector\Class_\DowngradeReadonlyAnonymousClassRector;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\DowngradePhp83\Rector\ClassConst\DowngradeTypedClassConstRector;
use Argtyper202511\Rector\DowngradePhp83\Rector\ClassConstFetch\DowngradeDynamicClassConstFetchRector;
use Argtyper202511\Rector\DowngradePhp83\Rector\FuncCall\DowngradeJsonValidateRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_82);
    $rectorConfig->rules([DowngradeTypedClassConstRector::class, DowngradeReadonlyAnonymousClassRector::class, DowngradeDynamicClassConstFetchRector::class, DowngradeJsonValidateRector::class]);
};
