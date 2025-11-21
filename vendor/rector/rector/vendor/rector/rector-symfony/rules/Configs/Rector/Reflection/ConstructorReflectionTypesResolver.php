<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\Configs\Rector\Reflection;

use Argtyper202511\PHPStan\Reflection\MethodReflection;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\ValueObject\MethodName;
final class ConstructorReflectionTypesResolver
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionProvider $reflectionProvider, ReflectionResolver $reflectionResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @return array<string, Type>|null
     */
    public function resolve(string $serviceClass) : ?array
    {
        if (!$this->reflectionProvider->hasClass($serviceClass)) {
            return null;
        }
        $constructorReflection = $this->reflectionResolver->resolveMethodReflection($serviceClass, MethodName::CONSTRUCT, null);
        if (!$constructorReflection instanceof MethodReflection) {
            return null;
        }
        return $this->resolveMethodReflectionParameterTypes($constructorReflection);
    }
    /**
     * @return array<string, Type>
     */
    private function resolveMethodReflectionParameterTypes(MethodReflection $methodReflection) : array
    {
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($methodReflection->getVariants());
        $constructorTypes = [];
        foreach ($extendedParametersAcceptor->getParameters() as $extendedParameterReflection) {
            $constructorTypes[$extendedParameterReflection->getName()] = $extendedParameterReflection->getType();
        }
        return $constructorTypes;
    }
}
