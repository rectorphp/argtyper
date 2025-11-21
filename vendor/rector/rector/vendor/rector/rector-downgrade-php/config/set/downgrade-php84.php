<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\DowngradePhp84\Rector\Expression\DowngradeArrayAllRector;
use Argtyper202511\Rector\DowngradePhp84\Rector\Expression\DowngradeArrayAnyRector;
use Argtyper202511\Rector\DowngradePhp84\Rector\Expression\DowngradeArrayFindKeyRector;
use Argtyper202511\Rector\DowngradePhp84\Rector\Expression\DowngradeArrayFindRector;
use Argtyper202511\Rector\DowngradePhp84\Rector\FuncCall\DowngradeRoundingModeEnumRector;
use Argtyper202511\Rector\DowngradePhp84\Rector\MethodCall\DowngradeNewMethodCallWithoutParenthesesRector;
use Argtyper202511\Rector\ValueObject\PhpVersion;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_83);
    $rectorConfig->rules([DowngradeNewMethodCallWithoutParenthesesRector::class, DowngradeRoundingModeEnumRector::class, DowngradeArrayAllRector::class, DowngradeArrayAnyRector::class, DowngradeArrayFindRector::class, DowngradeArrayFindKeyRector::class]);
};
