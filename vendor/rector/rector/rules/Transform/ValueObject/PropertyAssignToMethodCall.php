<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
/**
 * @deprecated as related rule is deprecated
 */
final class PropertyAssignToMethodCall
{
    /**
     * @readonly
     * @var string
     */
    private $class;
    /**
     * @readonly
     * @var string
     */
    private $oldPropertyName;
    /**
     * @readonly
     * @var string
     */
    private $newMethodName;
    public function __construct(string $class, string $oldPropertyName, string $newMethodName)
    {
        $this->class = $class;
        $this->oldPropertyName = $oldPropertyName;
        $this->newMethodName = $newMethodName;
        RectorAssert::className($class);
        RectorAssert::propertyName($oldPropertyName);
        RectorAssert::methodName($newMethodName);
    }
    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getOldPropertyName(): string
    {
        return $this->oldPropertyName;
    }
    public function getNewMethodName(): string
    {
        return $this->newMethodName;
    }
}
