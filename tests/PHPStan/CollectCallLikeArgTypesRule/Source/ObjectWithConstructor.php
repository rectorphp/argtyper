<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\Source;

final class ObjectWithConstructor
{
    public function __construct($value)
    {
    }
}
