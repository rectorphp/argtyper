<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\Source;

final class SomeObject
{
    public function getName(): string
    {
        return 'some name';
    }
}
