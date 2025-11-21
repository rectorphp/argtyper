<?php

declare(strict_types=1);

namespace PHPStan\CollectCallLikeArgTypesRule\Fixture;

use Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\Source\ObjectWithConstructor;

final class FloatAsInt
{
    public function run(): void
    {
        $firstArg = new ObjectWithConstructor(100.0);

        $secondArg = new ObjectWithConstructor(100.5);
    }
}
