<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan\Collectors;

use PHPStan\Collectors\Collector;
use PHPStan\Type\ErrorType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;

abstract class AbstractCallLikeTypeCollector
{
    protected function shouldSkipType(Type $type): bool
    {
        // unable to move to json for now, handle later
        if ($type instanceof ErrorType) {
            return true;
        }

        if ($type instanceof MixedType) {
            return true;
        }

        if ($type instanceof UnionType) {
            return true;
        }

        return $type instanceof IntersectionType;
    }
}
