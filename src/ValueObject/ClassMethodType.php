<?php

declare(strict_types=1);

namespace Rector\ArgTyper\ValueObject;

final class ClassMethodType
{
    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly string $type,
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