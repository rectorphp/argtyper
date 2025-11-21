<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\CollectFuncCallArgTypesRule\Fixture;

use function Rector\ArgTyper\Tests\PHPStan\CollectFuncCallArgTypesRule\Source\someFunction;

final class SimpleFunctionCall
{
    public function run(): void
    {
        someFunction(100, 200.0);
    }
}
