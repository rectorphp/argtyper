<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\NodeAnalyser;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ExtendedMethodReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PHPUnit\CodeQuality\Enum\NonAssertNonStaticMethods;
use Argtyper202511\Rector\PHPUnit\Enum\PHPUnitClassName;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
final class AssertMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeNameResolver $nodeNameResolver, ReflectionResolver $reflectionResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    public function detectTestCaseCall($call) : bool
    {
        $objectCaller = $call instanceof MethodCall ? $call->var : $call->class;
        if (!$this->nodeTypeResolver->isObjectType($objectCaller, new ObjectType('Argtyper202511\\PHPUnit\\Framework\\TestCase'))) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($call->name);
        if (\strncmp((string) $methodName, 'assert', \strlen('assert')) !== 0 && !\in_array($methodName, NonAssertNonStaticMethods::ALL, \true)) {
            return \false;
        }
        if ($call instanceof StaticCall && !$this->nodeNameResolver->isNames($call->class, ['static', 'self'])) {
            return \false;
        }
        $extendedMethodReflection = $this->resolveMethodReflection($call);
        if (!$extendedMethodReflection instanceof ExtendedMethodReflection) {
            return \false;
        }
        // only handle methods in TestCase or Assert class classes
        $declaringClassName = $extendedMethodReflection->getDeclaringClass()->getName();
        return \in_array($declaringClassName, [PHPUnitClassName::TEST_CASE, PHPUnitClassName::ASSERT]);
    }
    public function detectTestCaseCallForStatic(MethodCall $methodCall) : bool
    {
        if (!$this->detectTestCaseCall($methodCall)) {
            return \false;
        }
        $extendedMethodReflection = $this->resolveMethodReflection($methodCall);
        return $extendedMethodReflection instanceof ExtendedMethodReflection && $extendedMethodReflection->isStatic();
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $call
     */
    private function resolveMethodReflection($call) : ?ExtendedMethodReflection
    {
        $methodName = $this->nodeNameResolver->getName($call->name);
        if ($methodName === null) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($call);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        return $classReflection->getNativeMethod($methodName);
    }
}
