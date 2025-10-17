<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\ValueObject;

final readonly class FuncCallType
{
    public function __construct(
        private string $function,
        private int $position,
        private string $type,
    ) {
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
        return str_starts_with($this->type, 'object:');
    }
}
