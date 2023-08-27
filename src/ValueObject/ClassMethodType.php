<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\ValueObject;

final class ClassMethodType
{

    public function __construct(
        private readonly string $class,
        private readonly string $method,
        private readonly $type,
    )
    {
    }
}