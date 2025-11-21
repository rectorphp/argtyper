<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeCorrector;

use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
final class AccessoryNonEmptyStringTypeCorrector
{
    public function correct(Type $mainType) : Type
    {
        if (!$mainType instanceof IntersectionType) {
            return $mainType;
        }
        if (!$mainType->isNonEmptyString()->yes()) {
            return $mainType;
        }
        return new StringType();
    }
}
