<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\AttributeGroup\SingleConditionSecurityAttributeToIsGrantedRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\BinaryOp\RequestIsMainRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\BinaryOp\ResponseStatusCodeRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\Class_\EventListenerToEventSubscriberRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\Class_\InlineClassRoutePrefixRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\Class_\LoadValidatorMetadataToAnnotationRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\Class_\SplitAndSecurityAttributeToIsGrantedRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\ClassMethod\ActionSuffixRemoverRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\ClassMethod\ParamTypeFromRouteRequiredRegexRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\ClassMethod\RemoveUnusedRequestParamRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\MethodCall\AssertSameResponseCodeWithDebugContentsRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\MethodCall\LiteralGetToRequestClassConstantRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\MethodCall\ParameterBagTypedGetMethodCallRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\MethodCall\StringCastDebugResponseRector;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\Trait_\AddTraitGetterReturnTypeBasedOnSetterRequiredRector;
use Argtyper202511\Rector\Symfony\Symfony26\Rector\MethodCall\RedirectToRouteRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        RedirectToRouteRector::class,
        EventListenerToEventSubscriberRector::class,
        ResponseReturnTypeControllerActionRector::class,
        // int and string literals to const fetches
        ResponseStatusCodeRector::class,
        LiteralGetToRequestClassConstantRector::class,
        RemoveUnusedRequestParamRector::class,
        ParamTypeFromRouteRequiredRegexRector::class,
        ActionSuffixRemoverRector::class,
        LoadValidatorMetadataToAnnotationRector::class,
        // request method
        RequestIsMainRector::class,
        ParameterBagTypedGetMethodCallRector::class,
        // tests
        AssertSameResponseCodeWithDebugContentsRector::class,
        StringCastDebugResponseRector::class,
        // routing
        InlineClassRoutePrefixRector::class,
        // narrow attributes
        SingleConditionSecurityAttributeToIsGrantedRector::class,
        SplitAndSecurityAttributeToIsGrantedRector::class,
        AddTraitGetterReturnTypeBasedOnSetterRequiredRector::class,
    ]);
};
