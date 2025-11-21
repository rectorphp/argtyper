<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PHPStan;

use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\FunctionReflection;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptor;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
final class ParametersAcceptorSelectorVariantsWrapper
{
    /**
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $reflection
     * @param \PhpParser\Node\Expr\CallLike|\PhpParser\Node\FunctionLike $node
     */
    public static function select($reflection, $node, Scope $scope): ParametersAcceptor
    {
        $variants = $reflection->getVariants();
        if ($node instanceof FunctionLike) {
            return ParametersAcceptorSelector::combineAcceptors($variants);
        }
        if ($node->isFirstClassCallable()) {
            return ParametersAcceptorSelector::combineAcceptors($variants);
        }
        return ParametersAcceptorSelector::selectFromArgs($scope, $node->getArgs(), $variants);
    }
}
