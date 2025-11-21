<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Config\Level;

use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamArrayDocblockBasedOnCallableNativeFuncCallRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnArrayDocblockBasedOnArrayMapRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnDocblockForScalarArrayFromAssignsRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_\AddReturnDocblockDataProviderRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_\ClassMethodArrayDocblockParamFromLocalCallsRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromGetterReturnRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarArrayFromPropertyDefaultsRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\Class_\DocblockVarFromParamDocblockInConstructorRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockBasedOnArrayMapRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromAssignsParamToParamReferenceRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDataProviderRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddParamArrayDocblockFromDimFetchAccessRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForArrayDimAssignedObjectRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForCommonObjectDenominatorRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\AddReturnDocblockForJsonArrayRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockGetterReturnArrayFromPropertyDocblockVarRector;
use Argtyper202511\Rector\TypeDeclarationDocblocks\Rector\ClassMethod\DocblockReturnArrayFromDirectArrayInstanceRector;
final class TypeDeclarationDocblocksLevel
{
    /**
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [
        // start with rules based on native code
        // property var
        DocblockVarArrayFromPropertyDefaultsRector::class,
        // tests
        AddParamArrayDocblockFromDataProviderRector::class,
        AddReturnDocblockDataProviderRector::class,
        // param
        AddParamArrayDocblockFromDimFetchAccessRector::class,
        ClassMethodArrayDocblockParamFromLocalCallsRector::class,
        AddParamArrayDocblockBasedOnArrayMapRector::class,
        AddParamArrayDocblockFromAssignsParamToParamReferenceRector::class,
        AddParamArrayDocblockBasedOnCallableNativeFuncCallRector::class,
        // return
        AddReturnDocblockForCommonObjectDenominatorRector::class,
        AddReturnArrayDocblockBasedOnArrayMapRector::class,
        AddReturnDocblockForScalarArrayFromAssignsRector::class,
        DocblockReturnArrayFromDirectArrayInstanceRector::class,
        AddReturnDocblockForArrayDimAssignedObjectRector::class,
        AddReturnDocblockForJsonArrayRector::class,
        // move to rules based on existing docblocks, as more risky
        // property var
        DocblockVarFromParamDocblockInConstructorRector::class,
        DocblockVarArrayFromGetterReturnRector::class,
        // return
        DocblockGetterReturnArrayFromPropertyDocblockVarRector::class,
    ];
}
