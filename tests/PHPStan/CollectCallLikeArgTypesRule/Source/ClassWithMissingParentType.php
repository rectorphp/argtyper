<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\Source;

final class ClassWithMissingParentType extends MissingClass
{
    public function run($value)
    {
    }
}
