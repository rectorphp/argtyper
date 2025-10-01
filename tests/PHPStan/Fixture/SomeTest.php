<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\Fixture;

use PHPUnit\Framework\TestCase;
use Rector\ArgTyper\Tests\PHPStan\Source\SomeObject;

final class SomeTest extends TestCase
{
    public function test(SomeObject $someObject)
    {
        $this->assertSame('some name', $someObject->getName());
        $this->assertSame(2252, $someObject->getName());
    }
}
