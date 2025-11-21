<?php

declare(strict_types=1);

namespace Rector\ArgTyper\PHPStan;

use PhpParser\Node\Arg;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Constant\ConstantFloatType;
use PHPStan\Type\Constant\ConstantIntegerType;
use PHPStan\Type\Constant\ConstantStringType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerRangeType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;

final class TypeMapper
{
    public function mapToStringIfUseful(Arg $arg, Scope $scope): ?string
    {
        // work with native order only
        if ($arg->name instanceof Identifier) {
            return null;
        }

        $type = $scope->getType($arg->value);
        if ($this->shouldSkipType($type)) {
            return null;
        }

        if ($type instanceof TypeWithClassName) {
            return 'object:' . $type->getClassName();
        }

        $genericType = $this->mapConstantToGenericTypes($type);
        return $genericType::class;
    }

    private function mapConstantToGenericTypes(Type $type): Type
    {
        // correct to generic types
        if ($type instanceof IntegerRangeType) {
            return new IntegerType();
        }

        if ($type instanceof ClassStringType) {
            return new StringType();
        }

        // allow adding "array" type in case of passing multiple array and constant array types
        if ($type instanceof ConstantArrayType) {
            return new ArrayType(new MixedType(), new MixedType());
        }

        if ($type instanceof ArrayType) {
            return $type;
        }

        if ($type instanceof ConstantStringType) {
            return new StringType();
        }

        if ($type instanceof ConstantIntegerType) {
            return new IntegerType();
        }

        if ($type instanceof ConstantFloatType) {
            return new FloatType();
        }

        if ($type instanceof ConstantBooleanType) {
            return new BooleanType();
        }

        return $type;
    }

    private function shouldSkipType(Type $type): bool
    {
        // unable to move to json for now, handle later
        if ($type instanceof MixedType) {
            return true;
        }

        return $type instanceof UnionType || $type instanceof IntersectionType;
    }
}
