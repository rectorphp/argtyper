<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeCorrector;

use Argtyper202511\PHPStan\Type\Accessory\NonEmptyArrayType;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
final class AccessoryNonEmptyArrayTypeCorrector
{
    public function correct(Type $mainType): Type
    {
        if (!$mainType instanceof IntersectionType) {
            return $mainType;
        }
        if (!$mainType->isArray()->yes()) {
            return $mainType;
        }
        foreach ($mainType->getTypes() as $type) {
            if ($type instanceof NonEmptyArrayType) {
                return new ArrayType(new MixedType(), new MixedType());
            }
            if ($type instanceof ArrayType && $type->getIterableValueType() instanceof IntersectionType && $type->getIterableValueType()->isString()->yes()) {
                return new ArrayType(new MixedType(), new StringType());
            }
        }
        return $mainType;
    }
}
