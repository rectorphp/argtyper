<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\DumpFuncCallArgTypesRule\Fixture;

use function Rector\ArgTyper\Tests\PHPStan\DumpFuncCallArgTypesRule\Source\someFunction;

final class SimpleFunctionCall
{
    public function run(): void
    {
        someFunction(100, 200);
    }
}
