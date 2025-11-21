<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
/**
 * @deprecated as related rule is deprecated
 */
final class ReplaceParentCallByPropertyCall
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
    private $method;
    /**
     * @readonly
     * @var string
     */
    private $property;
    public function __construct(string $class, string $method, string $property)
    {
        $this->class = $class;
        $this->method = $method;
        $this->property = $property;
        RectorAssert::className($class);
        RectorAssert::methodName($method);
        RectorAssert::propertyName($property);
    }
    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function getProperty(): string
    {
        return $this->property;
    }
}
