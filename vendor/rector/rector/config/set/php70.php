<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php70\Rector\Assign\ListSplitStringRector;
use Argtyper202511\Rector\Php70\Rector\Assign\ListSwapArrayOrderRector;
use Argtyper202511\Rector\Php70\Rector\Break_\BreakNotInLoopOrSwitchToReturnRector;
use Argtyper202511\Rector\Php70\Rector\ClassMethod\Php4ConstructorRector;
use Argtyper202511\Rector\Php70\Rector\FuncCall\CallUserMethodRector;
use Argtyper202511\Rector\Php70\Rector\FuncCall\EregToPregMatchRector;
use Argtyper202511\Rector\Php70\Rector\FuncCall\MultiDirnameRector;
use Argtyper202511\Rector\Php70\Rector\FuncCall\RandomFunctionRector;
use Argtyper202511\Rector\Php70\Rector\FuncCall\RenameMktimeWithoutArgsToTimeRector;
use Argtyper202511\Rector\Php70\Rector\FunctionLike\ExceptionHandlerTypehintRector;
use Argtyper202511\Rector\Php70\Rector\If_\IfToSpaceshipRector;
use Argtyper202511\Rector\Php70\Rector\List_\EmptyListRector;
use Argtyper202511\Rector\Php70\Rector\MethodCall\ThisCallOnStaticMethodToStaticCallRector;
use Argtyper202511\Rector\Php70\Rector\StaticCall\StaticCallOnNonStaticToInstanceCallRector;
use Argtyper202511\Rector\Php70\Rector\StmtsAwareInterface\IfIssetToCoalescingRector;
use Argtyper202511\Rector\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector;
use Argtyper202511\Rector\Php70\Rector\Ternary\TernaryToNullCoalescingRector;
use Argtyper202511\Rector\Php70\Rector\Ternary\TernaryToSpaceshipRector;
use Argtyper202511\Rector\Php70\Rector\Variable\WrapVariableVariableNameInCurlyBracesRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([
        Php4ConstructorRector::class,
        TernaryToNullCoalescingRector::class,
        RandomFunctionRector::class,
        ExceptionHandlerTypehintRector::class,
        MultiDirnameRector::class,
        ListSplitStringRector::class,
        EmptyListRector::class,
        // be careful, run this just once, since it can keep swapping order back and forth
        ListSwapArrayOrderRector::class,
        CallUserMethodRector::class,
        EregToPregMatchRector::class,
        ReduceMultipleDefaultSwitchRector::class,
        TernaryToSpaceshipRector::class,
        WrapVariableVariableNameInCurlyBracesRector::class,
        IfToSpaceshipRector::class,
        StaticCallOnNonStaticToInstanceCallRector::class,
        ThisCallOnStaticMethodToStaticCallRector::class,
        BreakNotInLoopOrSwitchToReturnRector::class,
        RenameMktimeWithoutArgsToTimeRector::class,
        IfIssetToCoalescingRector::class,
    ]);
};
