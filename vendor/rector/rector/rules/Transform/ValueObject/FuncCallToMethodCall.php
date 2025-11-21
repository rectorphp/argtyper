<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
final class FuncCallToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $oldFuncName;
    /**
     * @readonly
     * @var string
     */
    private $newClassName;
    /**
     * @readonly
     * @var string
     */
    private $newMethodName;
    public function __construct(string $oldFuncName, string $newClassName, string $newMethodName)
    {
        $this->oldFuncName = $oldFuncName;
        $this->newClassName = $newClassName;
        $this->newMethodName = $newMethodName;
        RectorAssert::functionName($oldFuncName);
        RectorAssert::className($newClassName);
        RectorAssert::methodName($newMethodName);
    }
    public function getOldFuncName(): string
    {
        return $this->oldFuncName;
    }
    public function getNewObjectType(): ObjectType
    {
        return new ObjectType($this->newClassName);
    }
    public function getNewMethodName(): string
    {
        return $this->newMethodName;
    }
}
