<?php

declare (strict_types=1);
namespace Rector\DeadCode\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PHPStan\Reflection\FunctionReflection;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
use Argtyper202511\PHPStan\Type\NullType;
use Rector\Reflection\ReflectionResolver;
final class CallLikeParamDefaultResolver
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return int[]
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\FuncCall $callLike
     */
    public function resolveNullPositions($callLike): array
    {
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if (!$reflection instanceof MethodReflection && !$reflection instanceof FunctionReflection) {
            return [];
        }
        $nullPositions = [];
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($reflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $position => $extendedParameterReflection) {
            if (!$extendedParameterReflection->getDefaultValue() instanceof NullType) {
                continue;
            }
            $nullPositions[] = $position;
        }
        return $nullPositions;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\Expr\FuncCall $callLike
     */
    public function resolvePositionParameterByName($callLike, string $parameterName): ?int
    {
        $reflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($callLike);
        if (!$reflection instanceof MethodReflection && !$reflection instanceof FunctionReflection) {
            return null;
        }
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($reflection->getVariants());
        foreach ($extendedParametersAcceptor->getParameters() as $position => $extendedParameterReflection) {
            if ($extendedParameterReflection->getName() === $parameterName) {
                return $position;
            }
        }
        return null;
    }
}
