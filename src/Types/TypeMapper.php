<?php

declare (strict_types=1);
namespace TomasVotruba\SherlockTypes\Types;

use SherlockTypes202308\PHPStan\Type\BooleanType;
use SherlockTypes202308\PHPStan\Type\Constant\ConstantBooleanType;
use SherlockTypes202308\PHPStan\Type\Constant\ConstantFloatType;
use SherlockTypes202308\PHPStan\Type\Constant\ConstantIntegerType;
use SherlockTypes202308\PHPStan\Type\Constant\ConstantStringType;
use SherlockTypes202308\PHPStan\Type\IntegerType;
use SherlockTypes202308\PHPStan\Type\StringType;
use SherlockTypes202308\PHPStan\Type\Type;
final class TypeMapper
{
    public static function mapConstantToGenericTypes(Type $type) : Type
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
