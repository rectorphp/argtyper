<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Config\Level;

use Argtyper202511\Rector\CodeQuality\Rector\Assign\CombinedAssignRector;
use Argtyper202511\Rector\CodeQuality\Rector\BooleanAnd\RemoveUselessIsObjectCheckRector;
use Argtyper202511\Rector\CodeQuality\Rector\BooleanAnd\RepeatedAndNotEqualToNotInArrayRector;
use Argtyper202511\Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Argtyper202511\Rector\CodeQuality\Rector\BooleanNot\ReplaceConstantBooleanNotRector;
use Argtyper202511\Rector\CodeQuality\Rector\BooleanNot\ReplaceMultipleBooleanNotRector;
use Argtyper202511\Rector\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector;
use Argtyper202511\Rector\CodeQuality\Rector\BooleanOr\RepeatedOrEqualToInArrayRector;
use Argtyper202511\Rector\CodeQuality\Rector\Catch_\ThrowWithPreviousExceptionRector;
use Argtyper202511\Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Argtyper202511\Rector\CodeQuality\Rector\Class_\ConvertStaticToSelfRector;
use Argtyper202511\Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Argtyper202511\Rector\CodeQuality\Rector\Class_\RemoveReadonlyPropertyVisibilityOnReadonlyClassRector;
use Argtyper202511\Rector\CodeQuality\Rector\ClassConstFetch\VariableConstFetchToClassConstFetchRector;
use Argtyper202511\Rector\CodeQuality\Rector\ClassMethod\ExplicitReturnNullRector;
use Argtyper202511\Rector\CodeQuality\Rector\ClassMethod\InlineArrayReturnAssignRector;
use Argtyper202511\Rector\CodeQuality\Rector\ClassMethod\LocallyCalledStaticMethodToNonStaticRector;
use Argtyper202511\Rector\CodeQuality\Rector\ClassMethod\OptionalParametersAfterRequiredRector;
use Argtyper202511\Rector\CodeQuality\Rector\Concat\JoinStringConcatRector;
use Argtyper202511\Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Argtyper202511\Rector\CodeQuality\Rector\Equal\UseIdenticalOverEqualWithSameTypeRector;
use Argtyper202511\Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Argtyper202511\Rector\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector;
use Argtyper202511\Rector\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector;
use Argtyper202511\Rector\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector;
use Argtyper202511\Rector\CodeQuality\Rector\Foreach_\ForeachToInArrayRector;
use Argtyper202511\Rector\CodeQuality\Rector\Foreach_\SimplifyForeachToCoalescingRector;
use Argtyper202511\Rector\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\ArrayMergeOfNonArraysToSimpleArrayRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\CallUserFuncWithArrowFunctionToInlineRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\CompactToVariablesRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\IsAWithStringWithThirdArgumentRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\RemoveSoleValueSprintfRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\SetTypeToCastRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\SimplifyFuncGetArgsCountRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\SimplifyInArrayValuesRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\SimplifyRegexPatternRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\SimplifyStrposLowerRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\SortNamedParamRector;
use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\UnwrapSprintfOneArgumentRector;
use Argtyper202511\Rector\CodeQuality\Rector\Identical\BooleanNotIdenticalToNotIdenticalRector;
use Argtyper202511\Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Argtyper202511\Rector\CodeQuality\Rector\Identical\SimplifyArraySearchRector;
use Argtyper202511\Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Argtyper202511\Rector\CodeQuality\Rector\Identical\SimplifyConditionsRector;
use Argtyper202511\Rector\CodeQuality\Rector\Identical\StrlenZeroToIdenticalEmptyStringRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\CombineIfRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\CompleteMissingIfElseBracketRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\ConsecutiveNullCompareReturnsToNullCoalesceQueueRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\ExplicitBoolCompareRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\ShortenElseIfRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\SimplifyIfElseToTernaryRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\SimplifyIfNotNullReturnRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\SimplifyIfNullableReturnRector;
use Argtyper202511\Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Argtyper202511\Rector\CodeQuality\Rector\Include_\AbsolutizeRequireAndIncludePathRector;
use Argtyper202511\Rector\CodeQuality\Rector\Isset_\IssetOnPropertyObjectToPropertyExistsRector;
use Argtyper202511\Rector\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector;
use Argtyper202511\Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector;
use Argtyper202511\Rector\CodeQuality\Rector\New_\NewStaticToNewSelfRector;
use Argtyper202511\Rector\CodeQuality\Rector\NotEqual\CommonNotEqualRector;
use Argtyper202511\Rector\CodeQuality\Rector\NullsafeMethodCall\CleanupUnneededNullsafeOperatorRector;
use Argtyper202511\Rector\CodeQuality\Rector\Switch_\SingularSwitchToIfRector;
use Argtyper202511\Rector\CodeQuality\Rector\Switch_\SwitchTrueToIfRector;
use Argtyper202511\Rector\CodeQuality\Rector\Ternary\ArrayKeyExistsTernaryThenValueToCoalescingRector;
use Argtyper202511\Rector\CodeQuality\Rector\Ternary\NumberCompareToMaxFuncCallRector;
use Argtyper202511\Rector\CodeQuality\Rector\Ternary\SimplifyTautologyTernaryRector;
use Argtyper202511\Rector\CodeQuality\Rector\Ternary\SwitchNegatedTernaryRector;
use Argtyper202511\Rector\CodeQuality\Rector\Ternary\TernaryEmptyArrayArrayDimFetchToCoalesceRector;
use Argtyper202511\Rector\CodeQuality\Rector\Ternary\TernaryImplodeToImplodeRector;
use Argtyper202511\Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Argtyper202511\Rector\Php71\Rector\FuncCall\RemoveExtraParametersRector;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Argtyper202511\Rector\Strict\Rector\Empty_\DisallowedEmptyRuleFixerRector;
/**
 * Key 0 = level 0
 * Key 50 = level 50
 *
 * Start at 0, go slowly higher, one level per PR, and improve your rule coverage
 *
 * From the safest rules to more changing ones.
 *
 * This list can change in time, based on community feedback,
 * what rules are safer than others. The safest rules will be always in the top.
 */
final class CodeQualityLevel
{
    /**
     * The rule order matters, as it's used in the withCodeQualityLevel() method
     * Place the safest rules first, follow by more complex ones
     *
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [CombinedAssignRector::class, SimplifyEmptyArrayCheckRector::class, ReplaceMultipleBooleanNotRector::class, ReplaceConstantBooleanNotRector::class, ForeachToInArrayRector::class, RepeatedOrEqualToInArrayRector::class, RepeatedAndNotEqualToNotInArrayRector::class, SimplifyForeachToCoalescingRector::class, SimplifyFuncGetArgsCountRector::class, SimplifyInArrayValuesRector::class, SimplifyStrposLowerRector::class, SimplifyArraySearchRector::class, SimplifyConditionsRector::class, SimplifyIfNotNullReturnRector::class, SimplifyIfReturnBoolRector::class, UnnecessaryTernaryExpressionRector::class, RemoveExtraParametersRector::class, SimplifyDeMorganBinaryRector::class, SimplifyTautologyTernaryRector::class, SingleInArrayToCompareRector::class, SimplifyIfElseToTernaryRector::class, TernaryImplodeToImplodeRector::class, JoinStringConcatRector::class, ConsecutiveNullCompareReturnsToNullCoalesceQueueRector::class, ExplicitBoolCompareRector::class, CombineIfRector::class, UseIdenticalOverEqualWithSameTypeRector::class, SimplifyBoolIdenticalTrueRector::class, SimplifyRegexPatternRector::class, BooleanNotIdenticalToNotIdenticalRector::class, AndAssignsToSeparateLinesRector::class, CompactToVariablesRector::class, CompleteDynamicPropertiesRector::class, IsAWithStringWithThirdArgumentRector::class, StrlenZeroToIdenticalEmptyStringRector::class, ThrowWithPreviousExceptionRector::class, RemoveSoleValueSprintfRector::class, ShortenElseIfRector::class, ExplicitReturnNullRector::class, ArrayMergeOfNonArraysToSimpleArrayRector::class, ArrayKeyExistsTernaryThenValueToCoalescingRector::class, AbsolutizeRequireAndIncludePathRector::class, ChangeArrayPushToArrayAssignRector::class, ForRepeatedCountToOwnVariableRector::class, ForeachItemsAssignToEmptyArrayToAssignRector::class, InlineIfToExplicitIfRector::class, UnusedForeachValueToArrayKeysRector::class, CommonNotEqualRector::class, SetTypeToCastRector::class, LogicalToBooleanRector::class, VarToPublicPropertyRector::class, IssetOnPropertyObjectToPropertyExistsRector::class, NewStaticToNewSelfRector::class, UnwrapSprintfOneArgumentRector::class, VariableConstFetchToClassConstFetchRector::class, SwitchNegatedTernaryRector::class, SingularSwitchToIfRector::class, SimplifyIfNullableReturnRector::class, CallUserFuncWithArrowFunctionToInlineRector::class, FlipTypeControlToUseExclusiveTypeRector::class, InlineArrayReturnAssignRector::class, InlineIsAInstanceOfRector::class, TernaryFalseExpressionToIfRector::class, InlineConstructorDefaultToPropertyRector::class, TernaryEmptyArrayArrayDimFetchToCoalesceRector::class, OptionalParametersAfterRequiredRector::class, SimplifyEmptyCheckOnEmptyArrayRector::class, SwitchTrueToIfRector::class, CleanupUnneededNullsafeOperatorRector::class, DisallowedEmptyRuleFixerRector::class, LocallyCalledStaticMethodToNonStaticRector::class, NumberCompareToMaxFuncCallRector::class, CompleteMissingIfElseBracketRector::class, RemoveUselessIsObjectCheckRector::class, ConvertStaticToSelfRector::class, SortNamedParamRector::class, RemoveReadonlyPropertyVisibilityOnReadonlyClassRector::class];
    /**
     * @var array<class-string<RectorInterface>, mixed[]>
     */
    public const RULES_WITH_CONFIGURATION = [RenameFunctionRector::class => [
        'split' => 'explode',
        'join' => 'implode',
        'sizeof' => 'count',
        # https://www.php.net/manual/en/aliases.php
        'chop' => 'rtrim',
        'doubleval' => 'floatval',
        'gzputs' => 'gzwrite',
        'fputs' => 'fwrite',
        'ini_alter' => 'ini_set',
        'is_double' => 'is_float',
        'is_integer' => 'is_int',
        'is_long' => 'is_int',
        'is_real' => 'is_float',
        'is_writeable' => 'is_writable',
        'key_exists' => 'array_key_exists',
        'pos' => 'current',
        'strchr' => 'strstr',
        # mb
        'mbstrcut' => 'mb_strcut',
        'mbstrlen' => 'mb_strlen',
        'mbstrpos' => 'mb_strpos',
        'mbstrrpos' => 'mb_strrpos',
        'mbsubstr' => 'mb_substr',
    ]];
}
