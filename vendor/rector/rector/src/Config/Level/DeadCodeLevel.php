<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Config\Level;

use Argtyper202511\Rector\CodeQuality\Rector\FunctionLike\SimplifyUselessVariableRector;
use Argtyper202511\Rector\Contract\Rector\RectorInterface;
use Argtyper202511\Rector\DeadCode\Rector\Array_\RemoveDuplicatedArrayKeyRector;
use Argtyper202511\Rector\DeadCode\Rector\Assign\RemoveDoubleAssignRector;
use Argtyper202511\Rector\DeadCode\Rector\Assign\RemoveUnusedVariableAssignRector;
use Argtyper202511\Rector\DeadCode\Rector\Block\ReplaceBlockToItsStmtsRector;
use Argtyper202511\Rector\DeadCode\Rector\BooleanAnd\RemoveAndTrueRector;
use Argtyper202511\Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassConst\RemoveUnusedPrivateClassConstantRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassLike\RemoveTypedPropertyNonMockDocblockRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveArgumentFromDefaultParentCallRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveEmptyClassMethodRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveNullTagValueNodeRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodParameterRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPublicMethodParameterRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUselessAssignFromPropertyPromotionRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnExprInConstructRector;
use Argtyper202511\Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Argtyper202511\Rector\DeadCode\Rector\Closure\RemoveUnusedClosureVariableUseRector;
use Argtyper202511\Rector\DeadCode\Rector\Concat\RemoveConcatAutocastRector;
use Argtyper202511\Rector\DeadCode\Rector\ConstFetch\RemovePhpVersionIdCheckRector;
use Argtyper202511\Rector\DeadCode\Rector\Expression\RemoveDeadStmtRector;
use Argtyper202511\Rector\DeadCode\Rector\Expression\SimplifyMirrorAssignRector;
use Argtyper202511\Rector\DeadCode\Rector\For_\RemoveDeadContinueRector;
use Argtyper202511\Rector\DeadCode\Rector\For_\RemoveDeadIfForeachForRector;
use Argtyper202511\Rector\DeadCode\Rector\For_\RemoveDeadLoopRector;
use Argtyper202511\Rector\DeadCode\Rector\Foreach_\RemoveUnusedForeachKeyRector;
use Argtyper202511\Rector\DeadCode\Rector\FuncCall\RemoveFilterVarOnExactTypeRector;
use Argtyper202511\Rector\DeadCode\Rector\FunctionLike\NarrowWideUnionReturnTypeRector;
use Argtyper202511\Rector\DeadCode\Rector\FunctionLike\RemoveDeadReturnRector;
use Argtyper202511\Rector\DeadCode\Rector\If_\ReduceAlwaysFalseIfOrRector;
use Argtyper202511\Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Argtyper202511\Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Argtyper202511\Rector\DeadCode\Rector\If_\RemoveTypedPropertyDeadInstanceOfRector;
use Argtyper202511\Rector\DeadCode\Rector\If_\RemoveUnusedNonEmptyArrayBeforeForeachRector;
use Argtyper202511\Rector\DeadCode\Rector\If_\SimplifyIfElseWithSameContentRector;
use Argtyper202511\Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Argtyper202511\Rector\DeadCode\Rector\MethodCall\RemoveNullArgOnNullDefaultParamRector;
use Argtyper202511\Rector\DeadCode\Rector\Node\RemoveNonExistingVarAnnotationRector;
use Argtyper202511\Rector\DeadCode\Rector\Plus\RemoveDeadZeroAndOneOperationRector;
use Argtyper202511\Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Argtyper202511\Rector\DeadCode\Rector\Property\RemoveUselessReadOnlyTagRector;
use Argtyper202511\Rector\DeadCode\Rector\Property\RemoveUselessVarTagRector;
use Argtyper202511\Rector\DeadCode\Rector\PropertyProperty\RemoveNullPropertyInitializationRector;
use Argtyper202511\Rector\DeadCode\Rector\Return_\RemoveDeadConditionAboveReturnRector;
use Argtyper202511\Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Argtyper202511\Rector\DeadCode\Rector\Stmt\RemoveConditionExactReturnRector;
use Argtyper202511\Rector\DeadCode\Rector\Stmt\RemoveUnreachableStatementRector;
use Argtyper202511\Rector\DeadCode\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector;
use Argtyper202511\Rector\DeadCode\Rector\Ternary\TernaryToBooleanOrFalseToBooleanAndRector;
use Argtyper202511\Rector\DeadCode\Rector\TryCatch\RemoveDeadCatchRector;
use Argtyper202511\Rector\DeadCode\Rector\TryCatch\RemoveDeadTryCatchRector;
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
final class DeadCodeLevel
{
    /**
     * Mind that return type declarations are the safest to add,
     * followed by property, then params
     *
     * @var array<class-string<RectorInterface>>
     */
    public const RULES = [
        // easy picks
        RemoveUnusedForeachKeyRector::class,
        RemoveDuplicatedArrayKeyRector::class,
        RecastingRemovalRector::class,
        RemoveAndTrueRector::class,
        SimplifyMirrorAssignRector::class,
        RemoveDeadContinueRector::class,
        RemoveUnusedNonEmptyArrayBeforeForeachRector::class,
        RemoveNullPropertyInitializationRector::class,
        RemoveUselessReturnExprInConstructRector::class,
        ReplaceBlockToItsStmtsRector::class,
        RemoveFilterVarOnExactTypeRector::class,
        RemoveTypedPropertyDeadInstanceOfRector::class,
        TernaryToBooleanOrFalseToBooleanAndRector::class,
        RemoveDoubleAssignRector::class,
        RemoveUselessAssignFromPropertyPromotionRector::class,
        RemoveConcatAutocastRector::class,
        SimplifyIfElseWithSameContentRector::class,
        SimplifyUselessVariableRector::class,
        RemoveDeadZeroAndOneOperationRector::class,
        // docblock
        RemoveUselessParamTagRector::class,
        RemoveUselessReturnTagRector::class,
        RemoveUselessReadOnlyTagRector::class,
        RemoveNonExistingVarAnnotationRector::class,
        RemoveUselessVarTagRector::class,
        // prioritize safe belt on RemoveUseless*TagRector that registered previously first
        RemoveNullTagValueNodeRector::class,
        RemovePhpVersionIdCheckRector::class,
        RemoveTypedPropertyNonMockDocblockRector::class,
        RemoveAlwaysTrueIfConditionRector::class,
        ReduceAlwaysFalseIfOrRector::class,
        RemoveUnusedPrivateClassConstantRector::class,
        RemoveUnusedPrivatePropertyRector::class,
        RemoveUnusedClosureVariableUseRector::class,
        RemoveDuplicatedCaseInSwitchRector::class,
        RemoveDeadInstanceOfRector::class,
        RemoveDeadCatchRector::class,
        RemoveDeadTryCatchRector::class,
        RemoveDeadIfForeachForRector::class,
        RemoveConditionExactReturnRector::class,
        RemoveDeadStmtRector::class,
        UnwrapFutureCompatibleIfPhpVersionRector::class,
        RemoveParentCallWithoutParentRector::class,
        RemoveDeadConditionAboveReturnRector::class,
        RemoveDeadLoopRector::class,
        // removing methods could be risky if there is some magic loading them
        RemoveUnusedPromotedPropertyRector::class,
        RemoveUnusedPrivateMethodParameterRector::class,
        RemoveUnusedPublicMethodParameterRector::class,
        RemoveUnusedPrivateMethodRector::class,
        RemoveUnreachableStatementRector::class,
        RemoveUnusedVariableAssignRector::class,
        // this could break framework magic autowiring in some cases
        RemoveUnusedConstructorParamRector::class,
        RemoveEmptyClassMethodRector::class,
        RemoveDeadReturnRector::class,
        RemoveArgumentFromDefaultParentCallRector::class,
        RemoveNullArgOnNullDefaultParamRector::class,
        NarrowWideUnionReturnTypeRector::class,
    ];
}
