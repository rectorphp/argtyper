<?php

declare(strict_types=1);

namespace PHPStan\DumpCallLikeArgTypesRule\Fixture;

use Rector\ArgTyper\Tests\PHPStan\DumpCallLikeArgTypesRule\Source\ObjectWithConstructor;

final class FloatAsInt
{
    public function run(): void
    {
        $firstArg = new ObjectWithConstructor(100.0);

        $secondArg = new ObjectWithConstructor(100.5);
    }
}
