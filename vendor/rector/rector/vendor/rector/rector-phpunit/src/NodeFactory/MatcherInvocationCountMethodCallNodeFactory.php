<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\NodeFactory;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\PHPUnit\Enum\ConsecutiveVariable;
use Argtyper202511\Rector\PHPUnit\Enum\PHPUnitClassName;
/**
 * Handle silent rename "getInvocationCount()" to "numberOfInvocations()" in PHPUnit 10
 * https://github.com/sebastianbergmann/phpunit/commit/2ba8b7fded44a1a75cf5712a3b7310a8de0b6bb8#diff-3b464bb32b9187dd2d047fd1c21773aa32c19b20adebc083e1a49267c524a980
 */
final class MatcherInvocationCountMethodCallNodeFactory
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var string
     */
    private const GET_INVOCATION_COUNT = 'getInvocationCount';
    /**
     * @var string
     */
    private const NUMBER_OF_INVOCATIONS = 'numberOfInvocations';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function create() : MethodCall
    {
        $invocationMethodName = $this->getInvocationMethodName();
        $matcherVariable = new Variable(ConsecutiveVariable::MATCHER);
        return new MethodCall($matcherVariable, new Identifier($invocationMethodName));
    }
    private function getInvocationMethodName() : string
    {
        if (!$this->reflectionProvider->hasClass(PHPUnitClassName::INVOCATION_ORDER)) {
            // fallback to PHPUnit 9
            return self::GET_INVOCATION_COUNT;
        }
        $invocationOrderClassReflection = $this->reflectionProvider->getClass(PHPUnitClassName::INVOCATION_ORDER);
        // phpunit 10 naming
        if ($invocationOrderClassReflection->hasNativeMethod(self::GET_INVOCATION_COUNT)) {
            return self::GET_INVOCATION_COUNT;
        }
        // phpunit 9 naming
        return self::NUMBER_OF_INVOCATIONS;
    }
}
