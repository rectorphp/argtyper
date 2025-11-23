<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Rector\ValueObject;

final class ClassMethodType
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
     * @var string
     */
    private $type;
    /**
     * @readonly
     * @var bool
     */
    private $isNullable = \false;
    public function __construct(string $class, string $method, int $position, string $type, bool $isNullable = \false)
    {
        $this->class = $class;
        $this->method = $method;
        $this->position = $position;
        $this->type = $type;
        $this->isNullable = $isNullable;
    }
    public function getPosition(): int
    {
        return $this->position;
    }
    public function getClass(): string
    {
        return $this->class;
    }
    public function getMethod(): string
    {
        return $this->method;
    }
    public function getType(): string
    {
        return $this->type;
    }
    public function isObjectType(): bool
    {
        return strncmp($this->type, 'object:', strlen('object:')) === 0;
    }
    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
