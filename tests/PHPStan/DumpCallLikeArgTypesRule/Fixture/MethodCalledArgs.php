<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\DumpCallLikeArgTypesRule\Fixture;

use Rector\ArgTyper\Tests\PHPStan\DumpCallLikeArgTypesRule\Source\SomeObject;

final class MethodCalledArgs
{
    public function run(SomeObject $someObject): void
    {
        $someObject->setName('some name');
    }

    public function go(): void
    {
        SomeObject::setAge(100);
    }
}
