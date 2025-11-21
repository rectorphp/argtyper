<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
final class StaticCallToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $staticClass;
    /**
     * @readonly
     * @var string
     */
    private $staticMethod;
    /**
     * @readonly
     * @var string
     */
    private $classType;
    /**
     * @readonly
     * @var string
     */
    private $methodName;
    public function __construct(string $staticClass, string $staticMethod, string $classType, string $methodName)
    {
        $this->staticClass = $staticClass;
        $this->staticMethod = $staticMethod;
        $this->classType = $classType;
        $this->methodName = $methodName;
        RectorAssert::className($staticClass);
        // special char to match all method names
        if ($staticMethod !== '*') {
            RectorAssert::methodName($staticMethod);
        }
        RectorAssert::className($classType);
        if ($methodName !== '*') {
            RectorAssert::methodName($methodName);
        }
    }
    public function getClassObjectType() : ObjectType
    {
        return new ObjectType($this->classType);
    }
    public function getClassType() : string
    {
        return $this->classType;
    }
    public function getMethodName() : string
    {
        return $this->methodName;
    }
    public function isStaticCallMatch(StaticCall $staticCall) : bool
    {
        if (!$staticCall->class instanceof Name) {
            return \false;
        }
        $staticCallClassName = $staticCall->class->toString();
        if ($staticCallClassName !== $this->staticClass) {
            return \false;
        }
        if (!$staticCall->name instanceof Identifier) {
            return \false;
        }
        // all methods
        if ($this->staticMethod === '*') {
            return \true;
        }
        $staticCallMethodName = $staticCall->name->toString();
        return $staticCallMethodName === $this->staticMethod;
    }
}
