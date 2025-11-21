<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\TypeComparator;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\Constant\ConstantArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
/**
 * @see \Rector\Tests\NodeTypeResolver\TypeComparator\ArrayTypeComparatorTest
 */
final class ArrayTypeComparator
{
    /**
     * @param \PHPStan\Type\ArrayType|\PHPStan\Type\Constant\ConstantArrayType $checkedType
     * @param \PHPStan\Type\ArrayType|\PHPStan\Type\Constant\ConstantArrayType $mainType
     */
    public function isSubtype($checkedType, $mainType) : bool
    {
        if (!$checkedType instanceof ConstantArrayType && !$mainType instanceof ConstantArrayType) {
            return $mainType->isSuperTypeOf($checkedType)->yes();
        }
        $checkedKeyType = $checkedType->getIterableKeyType();
        $mainKeyType = $mainType->getIterableKeyType();
        if (!$mainKeyType instanceof MixedType && $mainKeyType->isSuperTypeOf($checkedKeyType)->yes()) {
            return \true;
        }
        $checkedItemType = $checkedType->getIterableValueType();
        $mainItemType = $mainType->getIterableValueType();
        return $checkedItemType->isSuperTypeOf($mainItemType)->yes();
    }
}
