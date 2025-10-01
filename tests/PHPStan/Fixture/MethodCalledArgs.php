<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\Fixture;

use Rector\ArgTyper\Tests\PHPStan\Source\SomeObject;

final class MethodCalledArgs
{
    public function run(SomeObject $someObject): void
    {
        $someObject->setName('some name');
    }
}
