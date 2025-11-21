<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\SideEffect;

use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Reflection\Native\NativeFunctionReflection;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class PureFunctionDetector
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function detect(FuncCall $funcCall): bool
    {
        $funcCallName = $this->nodeNameResolver->getName($funcCall);
        if ($funcCallName === null) {
            return \false;
        }
        $name = new Name($funcCallName);
        $hasFunction = $this->reflectionProvider->hasFunction($name, null);
        if (!$hasFunction) {
            return \false;
        }
        $functionReflection = $this->reflectionProvider->getFunction($name, null);
        if (!$functionReflection instanceof NativeFunctionReflection) {
            return \false;
        }
        // yes() and maybe() may have side effect
        return $functionReflection->hasSideEffects()->no();
    }
}
