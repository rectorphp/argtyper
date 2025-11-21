<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\ClosureFromCallableToFirstClassCallableRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\FunctionFirstClassCallableRector;
use Argtyper202511\Rector\CodingStyle\Rector\FunctionLike\FunctionLikeToFirstClassCallableRector;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php81\Rector\Array_\FirstClassCallableRector;
use Argtyper202511\Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Argtyper202511\Rector\Php81\Rector\Class_\SpatieEnumClassToEnumRector;
use Argtyper202511\Rector\Php81\Rector\ClassMethod\NewInInitializerRector;
use Argtyper202511\Rector\Php81\Rector\FuncCall\NullToStrictIntPregSlitFuncCallLimitArgRector;
use Argtyper202511\Rector\Php81\Rector\FuncCall\NullToStrictStringFuncCallArgRector;
use Argtyper202511\Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use Argtyper202511\Rector\Php81\Rector\MethodCall\RemoveReflectionSetAccessibleCallsRector;
use Argtyper202511\Rector\Php81\Rector\MethodCall\SpatieEnumMethodCallToEnumConstRector;
use Argtyper202511\Rector\Php81\Rector\New_\MyCLabsConstructorCallToEnumFromRector;
use Argtyper202511\Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        ReturnNeverTypeRector::class,
        MyCLabsClassToEnumRector::class,
        MyCLabsMethodCallToEnumConstRector::class,
        MyCLabsConstructorCallToEnumFromRector::class,
        ReadOnlyPropertyRector::class,
        SpatieEnumClassToEnumRector::class,
        SpatieEnumMethodCallToEnumConstRector::class,
        NullToStrictStringFuncCallArgRector::class,
        NullToStrictIntPregSlitFuncCallLimitArgRector::class,
        // array of local method call
        FirstClassCallableRector::class,
        // closure/arrow function
        FunctionLikeToFirstClassCallableRector::class,
        ClosureFromCallableToFirstClassCallableRector::class,
        FunctionFirstClassCallableRector::class,
        RemoveReflectionSetAccessibleCallsRector::class,
        NewInInitializerRector::class,
    ]);
};
