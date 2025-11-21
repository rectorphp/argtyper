<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\ValueObject;

final readonly class ClassMethodType
{
    public function __construct(
        private string $class,
        private string $method,
        private int $position,
        private string $type,
        private bool $isNullable = false,
    ) {
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
        return str_starts_with($this->type, 'object:');
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }
}
