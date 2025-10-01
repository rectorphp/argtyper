<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Rector\ValueObject;

final readonly class ClassMethodType
{
    public function __construct(
        private string $class,
        private string $method,
        private string $type,
    ) {
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
}