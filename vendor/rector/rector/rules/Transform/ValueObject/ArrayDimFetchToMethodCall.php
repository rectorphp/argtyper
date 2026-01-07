<?php

declare (strict_types=1);
namespace Rector\Transform\ValueObject;

use PHPStan\Type\ObjectType;
final class ArrayDimFetchToMethodCall
{
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $objectType;
    /**
     * @readonly
     * @var string
     */
    private $method;
    /**
     * @readonly
     * @var string|null
     */
    private $setMethod;
    /**
     * @readonly
     * @var string|null
     */
    private $existsMethod;
    /**
     * @readonly
     * @var string|null
     */
    private $unsetMethod;
    public function __construct(ObjectType $objectType, string $method, ?string $setMethod = null, ?string $existsMethod = null, ?string $unsetMethod = null)
    {
        $this->objectType = $objectType;
        $this->method = $method;
        // Optional methods for set, exists, and unset operations
        // if null, then these operations will not be transformed
        $this->setMethod = $setMethod;
        $this->existsMethod = $existsMethod;
        $this->unsetMethod = $unsetMethod;
    }
    public function getObjectType(): ObjectType
    {
        return $this->objectType;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function getSetMethod(): ?string
    {
        return $this->setMethod;
    }
    public function getExistsMethod(): ?string
    {
        return $this->existsMethod;
    }
    public function getUnsetMethod(): ?string
    {
        return $this->unsetMethod;
    }
}
