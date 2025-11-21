<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php84\Rector\Class_\DeprecatedAnnotationToDeprecatedAttributeRector;
use Argtyper202511\Rector\Php84\Rector\Foreach_\ForeachToArrayAllRector;
use Argtyper202511\Rector\Php84\Rector\Foreach_\ForeachToArrayAnyRector;
use Argtyper202511\Rector\Php84\Rector\Foreach_\ForeachToArrayFindKeyRector;
use Argtyper202511\Rector\Php84\Rector\Foreach_\ForeachToArrayFindRector;
use Argtyper202511\Rector\Php84\Rector\FuncCall\AddEscapeArgumentRector;
use Argtyper202511\Rector\Php84\Rector\FuncCall\RoundingModeEnumRector;
use Argtyper202511\Rector\Php84\Rector\MethodCall\NewMethodCallWithoutParenthesesRector;
use Argtyper202511\Rector\Php84\Rector\Param\ExplicitNullableParamTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ExplicitNullableParamTypeRector::class, RoundingModeEnumRector::class, AddEscapeArgumentRector::class, NewMethodCallWithoutParenthesesRector::class, DeprecatedAnnotationToDeprecatedAttributeRector::class, ForeachToArrayFindRector::class, ForeachToArrayFindKeyRector::class, ForeachToArrayAllRector::class, ForeachToArrayAnyRector::class]);
};
