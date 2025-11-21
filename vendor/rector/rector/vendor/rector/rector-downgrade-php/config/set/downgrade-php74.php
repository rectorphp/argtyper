<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\ClassMethod\DowngradeContravariantArgumentTypeRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\ClassMethod\DowngradeCovariantReturnTypeRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\Coalesce\DowngradeNullCoalescingOperatorRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\FuncCall\DowngradeArrayMergeCallWithoutArgumentsRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\FuncCall\DowngradeProcOpenArrayCommandArgRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\Identical\DowngradeFreadFwriteFalsyToNegationRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\Interface_\DowngradePreviouslyImplementedInterfaceRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\LNumber\DowngradeNumericLiteralSeparatorRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\MethodCall\DowngradeReflectionGetTypeRector;
use Argtyper202511\Rector\DowngradePhp74\Rector\Property\DowngradeTypedPropertyRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->phpVersion(PhpVersion::PHP_73);
    $rectorConfig->rules([
        DowngradeTypedPropertyRector::class,
        ArrowFunctionToAnonymousFunctionRector::class,
        DowngradeCovariantReturnTypeRector::class,
        DowngradeContravariantArgumentTypeRector::class,
        DowngradeNullCoalescingOperatorRector::class,
        DowngradeNumericLiteralSeparatorRector::class,
        DowngradeStripTagsCallWithArrayRector::class,
        // DowngradeArraySpreadRector::class,
        // already handled in PHP 8.1 set
        DowngradeArrayMergeCallWithoutArgumentsRector::class,
        DowngradeFreadFwriteFalsyToNegationRector::class,
        DowngradePreviouslyImplementedInterfaceRector::class,
        DowngradeReflectionGetTypeRector::class,
        DowngradeProcOpenArrayCommandArgRector::class,
    ]);
};
