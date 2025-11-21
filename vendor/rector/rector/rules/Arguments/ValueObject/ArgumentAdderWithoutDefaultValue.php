<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Arguments\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Validation\RectorAssert;
final class ArgumentAdderWithoutDefaultValue
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
     * @var string|null
     */
    private $argumentName;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $argumentType = null;
    /**
     * @readonly
     * @var string|null
     */
    private $scope;
    public function __construct(string $class, string $method, int $position, ?string $argumentName = null, ?\Argtyper202511\PHPStan\Type\Type $argumentType = null, ?string $scope = null)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->argumentName = $argumentName;
        $this->argumentType = $argumentType;
        $this->scope = $scope;
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
    public function getArgumentName(): ?string
    {
        return $this->argumentName;
    }
    public function getArgumentType(): ?Type
    {
        return $this->argumentType;
    }
    public function getScope(): ?string
    {
        return $this->scope;
    }
}
