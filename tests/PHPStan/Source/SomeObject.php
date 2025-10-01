<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\Source;

final class SomeObject
{
    private $name;

    public function setName($name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return 'some name';
    }
}
