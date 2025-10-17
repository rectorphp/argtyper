<?php

declare(strict_types=1);

namespace PHPStan\DumpCallLikeArgTypesRule\Fixture;

use PHPStan\DumpCallLikeArgTypesRule\Source\SomeObject;

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
