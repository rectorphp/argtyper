<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\Type;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\Constant\ConstantArrayType;
use Argtyper202511\PHPStan\Type\ConstantScalarType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
final class StaticTypeAnalyzer
{
    public function isAlwaysTruableType(Type $type): bool
    {
        if ($type instanceof MixedType) {
            return \false;
        }
        if ($type instanceof ConstantArrayType) {
            return \true;
        }
        if ($type instanceof ArrayType) {
            return $this->isAlwaysTruableArrayType($type);
        }
        if ($type instanceof UnionType && TypeCombinator::containsNull($type)) {
            return \false;
        }
        // always trueish
        if ($type instanceof ObjectType) {
            return \true;
        }
        if ($type instanceof ConstantScalarType && !$type->isNull()->yes()) {
            return (bool) $type->getValue();
        }
        if ($type->isScalar()->yes()) {
            return \false;
        }
        return $this->isAlwaysTruableUnionType($type);
    }
    private function isAlwaysTruableUnionType(Type $type): bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$this->isAlwaysTruableType($unionedType)) {
                return \false;
            }
        }
        return \true;
    }
    private function isAlwaysTruableArrayType(ArrayType $arrayType): bool
    {
        $itemType = $arrayType->getIterableValueType();
        if (!$itemType instanceof ConstantScalarType) {
            return \false;
        }
        return (bool) $itemType->getValue();
    }
}
