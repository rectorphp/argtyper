<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Assign\ArrayDimFetchAssignToAddCollectionCallRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Assign\ArrayOffsetSetToSetCollectionCallRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_\CompleteParamDocblockFromSetterToCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_\CompleteReturnDocblockFromToManyRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_\InitializeCollectionInConstructorRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Class_\RemoveNullFromInstantiatedArrayCollectionPropertyRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionGetterNativeTypeRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionParamTypeSetterToCollectionPropertyRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\CollectionSetterParamNativeTypeRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\NarrowArrayCollectionToCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\NarrowParamUnionToCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\NarrowReturnUnionToCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\RemoveNewArrayCollectionOutsideConstructorRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\RemoveNullFromNullableCollectionTypeRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\ReturnArrayToNewArrayCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod\ReturnCollectionDocblockRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Empty_\EmptyOnCollectionToIsEmptyCallRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Expression\RemoveAssertNotNullOnCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Expression\RemoveCoalesceAssignOnCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\FuncCall\ArrayMapOnCollectionToArrayRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\FuncCall\ArrayMergeOnCollectionToArrayRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\FuncCall\CurrentOnCollectionToArrayRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\FuncCall\InArrayOnCollectionToContainsCallRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\If_\RemoveIfCollectionIdenticalToNullRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\If_\RemoveIfInstanceofCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\If_\RemoveIsArrayOnCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\If_\RemoveUselessIsEmptyAssignRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\MethodCall\AssertNullOnCollectionToAssertEmptyRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\MethodCall\AssertSameCountOnCollectionToAssertCountRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\MethodCall\SetArrayToNewCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\New_\RemoveNewArrayCollectionWrapRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\NullsafeMethodCall\RemoveNullsafeOnCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Property\NarrowPropertyUnionToCollectionRector;
use Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Property\TypedPropertyFromToManyRelationTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    // rule that handle docblocks only, safer to apply
    $rectorConfig->import(__DIR__ . '/typed-collections-docblocks.php');
    $rectorConfig->rules([
        // init
        InitializeCollectionInConstructorRector::class,
        RemoveNullFromInstantiatedArrayCollectionPropertyRector::class,
        RemoveNewArrayCollectionOutsideConstructorRector::class,
        // cleanups
        RemoveCoalesceAssignOnCollectionRector::class,
        RemoveIfInstanceofCollectionRector::class,
        RemoveIsArrayOnCollectionRector::class,
        RemoveIfCollectionIdenticalToNullRector::class,
        // collection method calls
        ArrayDimFetchAssignToAddCollectionCallRector::class,
        ArrayOffsetSetToSetCollectionCallRector::class,
        ArrayMapOnCollectionToArrayRector::class,
        ArrayMergeOnCollectionToArrayRector::class,
        CurrentOnCollectionToArrayRector::class,
        EmptyOnCollectionToIsEmptyCallRector::class,
        InArrayOnCollectionToContainsCallRector::class,
        // native type declarations
        CollectionGetterNativeTypeRector::class,
        CollectionSetterParamNativeTypeRector::class,
        CollectionParamTypeSetterToCollectionPropertyRector::class,
        TypedPropertyFromToManyRelationTypeRector::class,
        RemoveNullFromNullableCollectionTypeRector::class,
        // docblocks
        NarrowArrayCollectionToCollectionRector::class,
        // @param docblock
        CompleteParamDocblockFromSetterToCollectionRector::class,
        NarrowParamUnionToCollectionRector::class,
        // @var docblock
        NarrowPropertyUnionToCollectionRector::class,
        // @return docblock
        NarrowReturnUnionToCollectionRector::class,
        CompleteReturnDocblockFromToManyRector::class,
        ReturnCollectionDocblockRector::class,
        // new ArrayCollection() wraps
        ReturnArrayToNewArrayCollectionRector::class,
        SetArrayToNewCollectionRector::class,
        RemoveNewArrayCollectionWrapRector::class,
        // cleanup
        RemoveNullsafeOnCollectionRector::class,
        RemoveUselessIsEmptyAssignRector::class,
        // test assertions
        RemoveAssertNotNullOnCollectionRector::class,
        AssertNullOnCollectionToAssertEmptyRector::class,
        AssertSameCountOnCollectionToAssertCountRector::class,
    ]);
};
