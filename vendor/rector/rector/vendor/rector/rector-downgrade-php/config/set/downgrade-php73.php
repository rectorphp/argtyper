<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\DowngradePhp73\Rector\ConstFetch\DowngradePhp73JsonConstRector;
use Argtyper202511\Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Argtyper202511\Rector\DowngradePhp73\Rector\FuncCall\DowngradeIsCountableRector;
use Argtyper202511\Rector\DowngradePhp73\Rector\FuncCall\DowngradeTrailingCommasInFunctionCallsRector;
use Argtyper202511\Rector\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector;
use Argtyper202511\Rector\DowngradePhp73\Rector\List_\DowngradeListReferenceAssignmentRector;
use Argtyper202511\Rector\DowngradePhp73\Rector\String_\DowngradeFlexibleHeredocSyntaxRector;
use Argtyper202511\Rector\DowngradePhp73\Rector\Unset_\DowngradeTrailingCommasInUnsetRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_72);
    $rectorConfig->rules([DowngradeFlexibleHeredocSyntaxRector::class, DowngradeListReferenceAssignmentRector::class, DowngradeTrailingCommasInFunctionCallsRector::class, DowngradeArrayKeyFirstLastRector::class, SetCookieOptionsArrayToArgumentsRector::class, DowngradeIsCountableRector::class, DowngradePhp73JsonConstRector::class, DowngradeTrailingCommasInUnsetRector::class]);
};
