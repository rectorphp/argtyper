<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Reflection\FunctionReflection;
use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Reflection\Native\NativeFunctionReflection;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use ReflectionFunction;
final class UnnamedArgumentResolver
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\DowngradePhp80\NodeAnalyzer\NamedToUnnamedArgs
     */
    private $namedToUnnamedArgs;
    public function __construct(NodeNameResolver $nodeNameResolver, \Argtyper202511\Rector\DowngradePhp80\NodeAnalyzer\NamedToUnnamedArgs $namedToUnnamedArgs)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->namedToUnnamedArgs = $namedToUnnamedArgs;
    }
    /**
     * @param Arg[] $currentArgs
     * @return Arg[]
     * @param \PHPStan\Reflection\FunctionReflection|\PHPStan\Reflection\MethodReflection $functionLikeReflection
     */
    public function resolveFromReflection($functionLikeReflection, array $currentArgs): array
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($functionLikeReflection->getVariants());
        $parameters = $extendedParametersAcceptor->getParameters();
        if ($functionLikeReflection instanceof NativeFunctionReflection) {
            $functionLikeReflection = new ReflectionFunction($functionLikeReflection->getName());
        }
        /** @var array<int, Arg> $unnamedArgs */
        $unnamedArgs = [];
        $toFillArgs = [];
        foreach ($currentArgs as $key => $arg) {
            if (!$arg->name instanceof Identifier) {
                $unnamedArgs[$key] = new Arg($arg->value, $arg->byRef, $arg->unpack, []);
                continue;
            }
            /** @var string $argName */
            $argName = $this->nodeNameResolver->getName($arg->name);
            $toFillArgs[] = $argName;
        }
        $unnamedArgs = $this->namedToUnnamedArgs->fillFromNamedArgs($parameters, $currentArgs, $toFillArgs, $unnamedArgs);
        $unnamedArgs = $this->namedToUnnamedArgs->fillFromJumpedNamedArgs($functionLikeReflection, $unnamedArgs, $parameters);
        ksort($unnamedArgs);
        return $unnamedArgs;
    }
}
