<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Rector\ValueObject;

final class FuncCallType
{
    /**
     * @readonly
     * @var string
     */
    private $function;
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
    public function __construct(string $function, int $position, string $type, bool $isNullable = \false)
    {
        $this->function = $function;
        $this->position = $position;
        $this->type = $type;
        $this->isNullable = $isNullable;
    }
    public function getFunction(): string
    {
        return $this->function;
    }
    public function getPosition(): int
    {
        return $this->position;
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
