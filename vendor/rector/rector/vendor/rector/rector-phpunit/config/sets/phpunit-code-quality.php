<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\AddParamTypeFromDependsRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\AddReturnTypeToDependedRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\ConstructClassMethodToSetUpTestCaseRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\NarrowUnusedSetUpDefinedPropertyRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\RemoveDataProviderParamKeysRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\SingleMockPropertyTypeRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\TestWithToDataProviderRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\TypeWillReturnCallableArrowFunctionRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\ClassMethod\AddInstanceofAssertForNullableInstanceRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\ClassMethod\DataProviderArrayItemsNewLinedRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\ClassMethod\EntityDocumentCreateMockToDirectNewRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\ClassMethod\RemoveEmptyTestMethodRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\ClassMethod\ReplaceTestAnnotationWithPrefixedFunctionRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Expression\AssertArrayCastedObjectToAssertSameRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\Foreach_\SimplifyForeachInstanceOfRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\FuncCall\AssertFuncCallToPHPUnitAssertRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertCompareOnCountableWithMethodToAssertCountRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertComparisonToSpecificMethodRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEqualsToSameRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertFalseStrposToContainsRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertIssetToSpecificMethodRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertNotOperatorRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertPropertyExistsRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertRegExpRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameBoolNullToSpecificMethodRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertSameTrueFalseToAssertTrueFalseRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertTrueFalseToSpecificMethodRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\FlipAssertRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\MatchAssertSameExpectedTypeRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\MergeWithCallableAndWillReturnRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\NarrowIdenticalWithConsecutiveRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\NarrowSingleWillReturnCallbackRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\RemoveExpectAnyFromMockRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\ScalarArgumentToExpectedParamTypeRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\SimplerWithIsInstanceOfRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\SingleWithConsecutiveToWithRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\StringCastAssertStringContainsStringRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\UseSpecificWillMethodRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\UseSpecificWithMethodRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\MethodCall\WithCallbackIdenticalToStandaloneAssertsRector;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Rector\StmtsAwareInterface\DeclareStrictTypesTestsRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit60\Rector\MethodCall\GetMockBuilderGetMockToCreateMockRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit90\Rector\MethodCall\ReplaceAtMethodWithDesiredMatcherRector;
use Argtyper202511\Rector\Privatization\Rector\Class_\FinalizeTestCaseClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        ConstructClassMethodToSetUpTestCaseRector::class,
        AssertSameTrueFalseToAssertTrueFalseRector::class,
        MatchAssertSameExpectedTypeRector::class,
        AssertEqualsToSameRector::class,
        PreferPHPUnitThisCallRector::class,
        YieldDataProviderRector::class,
        RemoveEmptyTestMethodRector::class,
        ReplaceTestAnnotationWithPrefixedFunctionRector::class,
        TestWithToDataProviderRector::class,
        AssertEqualsOrAssertSameFloatParameterToSpecificMethodsTypeRector::class,
        DataProviderArrayItemsNewLinedRector::class,
        // PHPUnit 11 reports warnings on typos + keys are rather noise than useful, fake system-keys for values
        RemoveDataProviderParamKeysRector::class,
        FlipAssertRector::class,
        // narrow with consecutive
        NarrowIdenticalWithConsecutiveRector::class,
        NarrowSingleWillReturnCallbackRector::class,
        SingleWithConsecutiveToWithRector::class,
        // type declarations
        TypeWillReturnCallableArrowFunctionRector::class,
        StringCastAssertStringContainsStringRector::class,
        AddParamTypeFromDependsRector::class,
        AddReturnTypeToDependedRector::class,
        ScalarArgumentToExpectedParamTypeRector::class,
        NarrowUnusedSetUpDefinedPropertyRector::class,
        // specific asserts
        AssertCompareOnCountableWithMethodToAssertCountRector::class,
        AssertComparisonToSpecificMethodRector::class,
        AssertNotOperatorRector::class,
        AssertTrueFalseToSpecificMethodRector::class,
        AssertSameBoolNullToSpecificMethodRector::class,
        AssertFalseStrposToContainsRector::class,
        AssertIssetToSpecificMethodRector::class,
        AssertInstanceOfComparisonRector::class,
        AssertPropertyExistsRector::class,
        AssertRegExpRector::class,
        AssertFuncCallToPHPUnitAssertRector::class,
        SimplifyForeachInstanceOfRector::class,
        UseSpecificWillMethodRector::class,
        UseSpecificWithMethodRector::class,
        AssertEmptyNullableObjectToAssertInstanceofRector::class,
        // avoid call on nullable object
        AddInstanceofAssertForNullableInstanceRector::class,
        AssertArrayCastedObjectToAssertSameRector::class,
        /**
         * Improve direct testing of your code, without mock creep. Make it simple, clear and easy to maintain:
         *
         * @see https://blog.frankdejonge.nl/testing-without-mocking-frameworks/
         * @see https://maksimivanov.com/posts/dont-mock-what-you-dont-own/
         * @see https://dev.to/mguinea/stop-using-mocking-libraries-2f2k
         * @see https://mnapoli.fr/anonymous-classes-in-tests/
         * @see https://steemit.com/php/@crell/don-t-use-mocking-libraries
         * @see https://davegebler.com/post/php/better-php-unit-testing-avoiding-mocks
         */
        RemoveExpectAnyFromMockRector::class,
        SingleMockPropertyTypeRector::class,
        SimplerWithIsInstanceOfRector::class,
        FinalizeTestCaseClassRector::class,
        DeclareStrictTypesTestsRector::class,
        WithCallbackIdenticalToStandaloneAssertsRector::class,
        MergeWithCallableAndWillReturnRector::class,
        // prefer simple mocking
        GetMockBuilderGetMockToCreateMockRector::class,
        EntityDocumentCreateMockToDirectNewRector::class,
        ReplaceAtMethodWithDesiredMatcherRector::class,
    ]);
};
