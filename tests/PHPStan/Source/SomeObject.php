<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\Source;

final class SomeObject
{
    private ?string $name = null;

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return 'some name';
    }
}
