<?php

declare(strict_types=1);

namespace PHPStan\DumpCallLikeArgTypesRule\Fixture;

use PHPStan\DumpCallLikeArgTypesRule\Source\ObjectWithConstructor;

final class ConstructorArgs
{
    public function run(): void
    {
        $firstArg = new ObjectWithConstructor(100);

        $secondArg = new ObjectWithConstructor(500);

        $thirdArg = new ObjectWithConstructor('ABC');
    }
}
