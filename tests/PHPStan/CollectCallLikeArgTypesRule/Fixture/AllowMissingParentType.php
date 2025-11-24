<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\Fixture;

use Rector\ArgTyper\Tests\PHPStan\CollectCallLikeArgTypesRule\Source\ClassWithMissingParentType;

final class AllowMissingParentType
{
    public function run(ClassWithMissingParentType $classWithMissingParentType): void
    {
        $classWithMissingParentType->run(100);

        $classWithMissingParentType->parentCall(100);
    }

    public function runAgain(MissingClass $missingClass)
    {
        $missingClass->run(200);
    }
}
