<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Config\Level;

use Argtyper202511\Rector\CodeQuality\Rector\Class_\ReturnIteratorInDataProviderRector;
use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\Symfony\CodeQuality\Rector\ClassMethod\ResponseReturnTypeControllerActionRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ArrowFunction\AddArrowFunctionReturnTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\AddTestsVoidReturnTypeWhereNoReturnRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\ChildDoctrineRepositoryClassTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\MergeDateTimePropertyTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\ObjectTypedPropertyFromJMSSerializerAttributeTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\PropertyTypeFromStrictSetterGetterRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\ReturnTypeFromStrictTernaryRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\ScalarTypedPropertyFromJMSSerializerAttributeTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\TypedPropertyFromDocblockSetUpDefinedRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Class_\TypedStaticPropertyInBehatContextRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamFromDimFetchKeyUseRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamStringTypeFromSprintfUseRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeBasedOnPHPUnitDataProviderRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeFromPropertyTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationBasedOnParentClassMethodRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeFromTryCatchTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanConstReturnsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\BoolReturnTypeFromBooleanStrictReturnsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\KnownMagicClassMethodTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\NarrowObjectReturnTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictReturnsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\NumericReturnTypeFromStrictScalarReturnsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByMethodCallTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ParamTypeByParentCallTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnNullableTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromMockObjectRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnCastRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnDirectArrayRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromReturnNewRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictConstantReturnRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictFluentReturnRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNativeCallRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictParamRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromSymfonySerializerRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\ReturnUnionTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\StrictArrayParamDimFetchRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\StrictStringParamConcatRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictScalarReturnsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\StringReturnTypeFromStrictStringReturnsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Closure\AddClosureNeverReturnTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Closure\AddClosureVoidReturnTypeWhereNoReturnRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Closure\ClosureReturnTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\FuncCall\AddArrayFunctionClosureParamTypeRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\FuncCall\AddArrowFunctionParamArrayWhereDimFetchRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Function_\AddFunctionVoidReturnTypeWhereNoReturnRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayMapRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeForArrayReduceRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike\AddClosureParamTypeFromIterableMethodCallRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike\AddParamTypeSplFixedArrayRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\FunctionLike\AddReturnTypeDeclarationFromYieldsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictSetUpRector;
final class TypeDeclarationLevel
{
    /**
     * The rule order matters, as its used in withTypeCoverageLevel() method
     * Place the safest rules first, follow by more complex ones
     *
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [
        // php 7.1, start with closure first, as safest
        AddClosureVoidReturnTypeWhereNoReturnRector::class,
        AddFunctionVoidReturnTypeWhereNoReturnRector::class,
        AddTestsVoidReturnTypeWhereNoReturnRector::class,
        ReturnIteratorInDataProviderRector::class,
        ReturnTypeFromMockObjectRector::class,
        TypedPropertyFromCreateMockAssignRector::class,
        AddArrowFunctionReturnTypeRector::class,
        BoolReturnTypeFromBooleanConstReturnsRector::class,
        ReturnTypeFromStrictNewArrayRector::class,
        // scalar and array from constant
        ReturnTypeFromStrictConstantReturnRector::class,
        StringReturnTypeFromStrictScalarReturnsRector::class,
        NumericReturnTypeFromStrictScalarReturnsRector::class,
        BoolReturnTypeFromBooleanStrictReturnsRector::class,
        StringReturnTypeFromStrictStringReturnsRector::class,
        NumericReturnTypeFromStrictReturnsRector::class,
        ReturnTypeFromStrictTernaryRector::class,
        ReturnTypeFromReturnDirectArrayRector::class,
        ResponseReturnTypeControllerActionRector::class,
        ReturnTypeFromReturnNewRector::class,
        ReturnTypeFromReturnCastRector::class,
        ReturnTypeFromSymfonySerializerRector::class,
        AddVoidReturnTypeWhereNoReturnRector::class,
        ReturnTypeFromStrictTypedPropertyRector::class,
        ReturnNullableTypeRector::class,
        // php 7.4
        EmptyOnNullableObjectToInstanceOfRector::class,
        // php 7.4
        TypedPropertyFromStrictConstructorRector::class,
        AddParamTypeSplFixedArrayRector::class,
        AddReturnTypeDeclarationFromYieldsRector::class,
        AddParamTypeBasedOnPHPUnitDataProviderRector::class,
        TypedPropertyFromStrictSetUpRector::class,
        ReturnTypeFromStrictNativeCallRector::class,
        AddReturnTypeFromTryCatchTypeRector::class,
        ReturnTypeFromStrictTypedCallRector::class,
        ChildDoctrineRepositoryClassTypeRector::class,
        // php native types
        KnownMagicClassMethodTypeRector::class,
        // param
        AddMethodCallBasedStrictParamTypeRector::class,
        ParamTypeByParentCallTypeRector::class,
        NarrowObjectReturnTypeRector::class,
        // multi types (nullable, union)
        ReturnUnionTypeRector::class,
        // closures
        AddClosureNeverReturnTypeRector::class,
        AddClosureParamTypeForArrayMapRector::class,
        AddClosureParamTypeForArrayReduceRector::class,
        ClosureReturnTypeRector::class,
        AddArrowFunctionParamArrayWhereDimFetchRector::class,
        // more risky rules
        ReturnTypeFromStrictParamRector::class,
        AddParamTypeFromPropertyTypeRector::class,
        MergeDateTimePropertyTypeDeclarationRector::class,
        PropertyTypeFromStrictSetterGetterRector::class,
        ParamTypeByMethodCallTypeRector::class,
        TypedPropertyFromAssignsRector::class,
        AddReturnTypeDeclarationBasedOnParentClassMethodRector::class,
        ReturnTypeFromStrictFluentReturnRector::class,
        ReturnNeverTypeRector::class,
        StrictStringParamConcatRector::class,
        // jms attributes
        ObjectTypedPropertyFromJMSSerializerAttributeTypeRector::class,
        ScalarTypedPropertyFromJMSSerializerAttributeTypeRector::class,
        // array parameter from dim fetch assign inside
        StrictArrayParamDimFetchRector::class,
        AddParamFromDimFetchKeyUseRector::class,
        AddParamStringTypeFromSprintfUseRector::class,
        // possibly based on docblocks, but also helpful, intentionally last
        AddArrayFunctionClosureParamTypeRector::class,
        TypedPropertyFromDocblockSetUpDefinedRector::class,
        AddClosureParamTypeFromIterableMethodCallRector::class,
        TypedStaticPropertyInBehatContextRector::class,
    ];
}
