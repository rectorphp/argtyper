<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Removing\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
final class ArgumentRemover
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
     * @var int
     */
    private $position;
    /**
     * @readonly
     * @var mixed
     */
    private $value;
    /**
     * @param mixed $value
     */
    public function __construct(string $class, string $method, int $position, $value)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->value = $value;
        RectorAssert::className($class);
    }
    public function getObjectType(): ObjectType
    {
        return new ObjectType($this->class);
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function getPosition(): int
    {
        return $this->position;
    }
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
