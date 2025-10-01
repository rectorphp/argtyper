<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Types;

use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;

final class TypeMapper
{
    public static function mapConstantToGenericTypes(Type $type): Type
    {
        // correct to generic types
        if ($type instanceof ConstantStringType) {
            return new StringType();
        }

        if ($type instanceof ConstantIntegerType) {
            return new IntegerType();
        }

        if ($type instanceof ConstantFloatType) {
            return new IntegerType();
        }

        if ($type instanceof ConstantBooleanType) {
            return new BooleanType();
        }

        return $type;
    }
}