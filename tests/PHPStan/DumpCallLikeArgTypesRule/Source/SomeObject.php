<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\DumpCallLikeArgTypesRule\Source;

final class SomeObject
{
    private $name;

    private static $age;

    public function setName($name): void
    {
        $this->name = $name;
    }

    public static function setAge($age): void
    {
        self::$age = $age;
    }
}
