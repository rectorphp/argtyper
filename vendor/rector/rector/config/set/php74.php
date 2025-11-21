<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PhpParser\Node\Expr\Cast\Double;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php74\Rector\ArrayDimFetch\CurlyToSquareBracketArrayStringRector;
use Argtyper202511\Rector\Php74\Rector\Assign\NullCoalescingOperatorRector;
use Argtyper202511\Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Argtyper202511\Rector\Php74\Rector\FuncCall\ArrayKeyExistsOnPropertyRector;
use Argtyper202511\Rector\Php74\Rector\FuncCall\FilterVarToAddSlashesRector;
use Argtyper202511\Rector\Php74\Rector\FuncCall\HebrevcToNl2brHebrevRector;
use Argtyper202511\Rector\Php74\Rector\FuncCall\MbStrrposEncodingArgumentPositionRector;
use Argtyper202511\Rector\Php74\Rector\FuncCall\MoneyFormatToNumberFormatRector;
use Argtyper202511\Rector\Php74\Rector\FuncCall\RestoreIncludePathToIniRestoreRector;
use Argtyper202511\Rector\Php74\Rector\Property\RestoreDefaultNullToNullableTypePropertyRector;
use Argtyper202511\Rector\Php74\Rector\StaticCall\ExportToReflectionFunctionRector;
use Argtyper202511\Rector\Php74\Rector\Ternary\ParenthesizeNestedTernaryRector;
use Argtyper202511\Rector\Renaming\Rector\Cast\RenameCastRector;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameCast;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, [
        #the_real_type
        # https://wiki.php.net/rfc/deprecations_php_7_4
        'is_real' => 'is_float',
    ]);
    $rectorConfig->rules([ArrayKeyExistsOnPropertyRector::class, FilterVarToAddSlashesRector::class, ExportToReflectionFunctionRector::class, MbStrrposEncodingArgumentPositionRector::class, NullCoalescingOperatorRector::class, ClosureToArrowFunctionRector::class, RestoreDefaultNullToNullableTypePropertyRector::class, CurlyToSquareBracketArrayStringRector::class, MoneyFormatToNumberFormatRector::class, ParenthesizeNestedTernaryRector::class, RestoreIncludePathToIniRestoreRector::class, HebrevcToNl2brHebrevRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameCastRector::class, [new RenameCast(Double::class, Double::KIND_REAL, Double::KIND_FLOAT)]);
};
