<?php

declare (strict_types=1);
namespace Rector\ArgTyper\PHPStan;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\ClassStringType;
use Argtyper202511\PHPStan\Type\Constant\ConstantArrayType;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\Constant\ConstantFloatType;
use Argtyper202511\PHPStan\Type\Constant\ConstantIntegerType;
use Argtyper202511\PHPStan\Type\Constant\ConstantStringType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerRangeType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeWithClassName;
use Argtyper202511\PHPStan\Type\UnionType;
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
        return get_class($genericType);
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
            return \true;
        }
        return $type instanceof UnionType || $type instanceof IntersectionType;
    }
}
