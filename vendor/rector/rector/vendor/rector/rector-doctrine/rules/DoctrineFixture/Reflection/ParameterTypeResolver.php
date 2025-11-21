<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\DoctrineFixture\Reflection;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
final class ParameterTypeResolver
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveCallerFirstParameterObjectType(MethodCall $methodCall): ?ObjectType
    {
        $callerType = $this->nodeTypeResolver->getType($methodCall->var);
        if (!$callerType instanceof ObjectType) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($callerType->getClassName())) {
            return null;
        }
        $callerClassReflection = $this->reflectionProvider->getClass($callerType->getClassName());
        $callerMethodName = $this->nodeNameResolver->getName($methodCall->name);
        if (!is_string($callerMethodName)) {
            return null;
        }
        $scope = ScopeFetcher::fetch($methodCall);
        $extendedMethodReflection = $callerClassReflection->getMethod($callerMethodName, $scope);
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $firstParameter = $extendedParametersAcceptor->getParameters()[0];
        $parameterType = $firstParameter->getType();
        $parameterType = TypeCombinator::removeNull($parameterType);
        if (!$parameterType instanceof ObjectType) {
            return null;
        }
        return $parameterType;
    }
}
