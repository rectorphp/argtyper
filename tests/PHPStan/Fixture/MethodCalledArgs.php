<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\ArgTyper\Tests\PHPStan\Source\SomeObject;

final class MethodCalledArgs
{
    public function run(SomeObject $someObject)
    {
        $someObject->setName('some name');
    }
}
