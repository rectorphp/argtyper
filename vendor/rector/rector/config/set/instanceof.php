<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\CodeQuality\Rector\FuncCall\InlineIsAInstanceOfRector;
use Argtyper202511\Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Argtyper202511\Rector\Instanceof_\Rector\Ternary\FlipNegatedTernaryInstanceofRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\BooleanAnd\BinaryOpNullableToInstanceofRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\Empty_\EmptyOnNullableObjectToInstanceOfRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\While_\WhileNullableToInstanceofRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([EmptyOnNullableObjectToInstanceOfRector::class, InlineIsAInstanceOfRector::class, FlipTypeControlToUseExclusiveTypeRector::class, RemoveDeadInstanceOfRector::class, FlipNegatedTernaryInstanceofRector::class, BinaryOpNullableToInstanceofRector::class, WhileNullableToInstanceofRector::class]);
};
