<?php

declare(strict_types=1);

namespace TomasVotruba\SherlockTypes\Tests\PHPStan\Fixture;

use PHPUnit\Framework\TestCase;
use TomasVotruba\SherlockTypes\Tests\PHPStan\Source\SomeObject;

final class SomeTest extends TestCase
{
    public function test(SomeObject $someObject)
    {
        $this->assertSame('some name', $someObject->getName());
        $this->assertSame(2252, $someObject->getName());
    }
}
