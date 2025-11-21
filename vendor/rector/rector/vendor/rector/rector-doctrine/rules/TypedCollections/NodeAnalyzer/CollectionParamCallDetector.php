<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
use Argtyper202511\PHPStan\Reflection\Php\PhpParameterReflection;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\ThisType;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\ValueObject\MethodName;
final class CollectionParamCallDetector
{
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
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver, ReflectionProvider $reflectionProvider)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_ $callLike
     */
    public function detect($callLike, int $position): bool
    {
        if ($callLike instanceof StaticCall) {
            $callerType = $this->nodeTypeResolver->getType($callLike->class);
            $methodName = $this->nodeNameResolver->getName($callLike->name);
        } elseif ($callLike instanceof MethodCall) {
            // does setter method require a collection?
            $callerType = $this->nodeTypeResolver->getType($callLike->var);
            $methodName = $this->nodeNameResolver->getName($callLike->name);
        } else {
            $callerType = $this->nodeTypeResolver->getType($callLike->class);
            $methodName = MethodName::CONSTRUCT;
        }
        $callerType = TypeCombinator::removeNull($callerType);
        // to support same-class calls as well
        if ($callerType instanceof ThisType) {
            $callerType = $callerType->getStaticObjectType();
        }
        if (!$callerType instanceof ObjectType) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($callerType->getClassName())) {
            return \false;
        }
        $classReflection = $this->reflectionProvider->getClass($callerType->getClassName());
        if ($methodName === null) {
            return \false;
        }
        $scope = ScopeFetcher::fetch($callLike);
        if (!$classReflection->hasMethod($methodName)) {
            return \false;
        }
        $extendedMethodReflection = $classReflection->getMethod($methodName, $scope);
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        $activeParameterReflection = $extendedParametersAcceptor->getParameters()[$position] ?? null;
        if (!$activeParameterReflection instanceof PhpParameterReflection) {
            return \false;
        }
        $parameterType = $activeParameterReflection->getType();
        // to include nullables
        $parameterType = TypeCombinator::removeNull($parameterType);
        if (!$parameterType instanceof ObjectType) {
            return \false;
        }
        return $parameterType->isInstanceOf(DoctrineClass::COLLECTION)->yes();
    }
}
