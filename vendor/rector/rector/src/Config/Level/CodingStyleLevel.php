<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Config\Level;

use Argtyper202511\Rector\CodingStyle\Rector\Assign\SplitDoubleAssignRector;
use Argtyper202511\Rector\CodingStyle\Rector\Catch_\CatchExceptionNameMatchingTypeRector;
use Argtyper202511\Rector\CodingStyle\Rector\ClassConst\RemoveFinalFromConstRector;
use Argtyper202511\Rector\CodingStyle\Rector\ClassConst\SplitGroupedClassConstantsRector;
use Argtyper202511\Rector\CodingStyle\Rector\ClassMethod\BinaryOpStandaloneAssignsToDirectRector;
use Argtyper202511\Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Argtyper202511\Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Argtyper202511\Rector\CodingStyle\Rector\ClassMethod\NewlineBeforeNewAssignSetRector;
use Argtyper202511\Rector\CodingStyle\Rector\Encapsed\EncapsedStringsToSprintfRector;
use Argtyper202511\Rector\CodingStyle\Rector\Encapsed\WrapEncapsedVariableInCurlyBracesRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\CallUserFuncArrayToVariadicRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\CallUserFuncToMethodCallRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\ConsistentImplodeRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\CountArrayToEmptyArrayComparisonRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\StrictArraySearchRector;
use Argtyper202511\Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Argtyper202511\Rector\CodingStyle\Rector\If_\NullableCompareToNullRector;
use Argtyper202511\Rector\CodingStyle\Rector\Property\SplitGroupedPropertiesRector;
use Argtyper202511\Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Argtyper202511\Rector\CodingStyle\Rector\Stmt\RemoveUselessAliasInUseStatementRector;
use Argtyper202511\Rector\CodingStyle\Rector\String_\SymplifyQuoteEscapeRector;
use Argtyper202511\Rector\CodingStyle\Rector\String_\UseClassKeywordForClassNameResolutionRector;
use Argtyper202511\Rector\CodingStyle\Rector\Ternary\TernaryConditionVariableAssignmentRector;
use Argtyper202511\Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Argtyper202511\Rector\Transform\Rector\FuncCall\FuncCallToConstFetchRector;
use Argtyper202511\Rector\Visibility\Rector\ClassMethod\ExplicitPublicClassMethodRector;
/**
 * Key 0 = level 0
 * Key 50 = level 50
 *
 * Start at 0, go slowly higher, one level per PR, and improve your rule coverage
 *
 * From the safest rules to more changing ones.
 *
 * This list can change in time, based on community feedback,
 * what rules are safer than other. The safest rules will be always in the top.
 */
final class CodingStyleLevel
{
    /**
     * The rule order matters, as its used in withCodingStyleLevel() method
     * Place the safest rules first, followed by more complex ones
     *
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [SeparateMultiUseImportsRector::class, NewlineAfterStatementRector::class, RemoveFinalFromConstRector::class, NullableCompareToNullRector::class, ConsistentImplodeRector::class, TernaryConditionVariableAssignmentRector::class, SymplifyQuoteEscapeRector::class, StringClassNameToClassConstantRector::class, CatchExceptionNameMatchingTypeRector::class, SplitDoubleAssignRector::class, EncapsedStringsToSprintfRector::class, WrapEncapsedVariableInCurlyBracesRector::class, NewlineBeforeNewAssignSetRector::class, MakeInheritedMethodVisibilitySameAsParentRector::class, CallUserFuncArrayToVariadicRector::class, VersionCompareFuncCallToConstantRector::class, CountArrayToEmptyArrayComparisonRector::class, CallUserFuncToMethodCallRector::class, FuncGetArgsToVariadicParamRector::class, StrictArraySearchRector::class, UseClassKeywordForClassNameResolutionRector::class, SplitGroupedPropertiesRector::class, SplitGroupedClassConstantsRector::class, ExplicitPublicClassMethodRector::class, RemoveUselessAliasInUseStatementRector::class, BinaryOpStandaloneAssignsToDirectRector::class];
    /**
     * @var array<class-string<RectorInterface>, mixed[]>
     */
    public const RULES_WITH_CONFIGURATION = [FuncCallToConstFetchRector::class => ['php_sapi_name' => 'PHP_SAPI', 'pi' => 'M_PI']];
}
