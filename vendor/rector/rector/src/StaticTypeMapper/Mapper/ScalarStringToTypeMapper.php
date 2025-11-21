<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\Mapper;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PHPStan\Type\Accessory\AccessoryArrayListType;
use Argtyper202511\PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use Argtyper202511\PHPStan\Type\Accessory\NonEmptyArrayType;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\CallableType;
use Argtyper202511\PHPStan\Type\ClassStringType;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerRangeType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\IterableType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NeverType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\ObjectWithoutClassType;
use Argtyper202511\PHPStan\Type\ResourceType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\VoidType;
final class ScalarStringToTypeMapper
{
    /**
     * @var array<class-string<Type>, string[]>
     */
    private const SCALAR_NAME_BY_TYPE = [StringType::class => ['string'], AccessoryNonEmptyStringType::class => ['non-empty-string'], NonEmptyArrayType::class => ['non-empty-array'], ClassStringType::class => ['class-string'], FloatType::class => ['float', 'real', 'double'], IntegerType::class => ['int', 'integer'], BooleanType::class => ['bool', 'boolean'], NullType::class => ['null'], VoidType::class => ['void'], ResourceType::class => ['resource'], CallableType::class => ['callback', 'callable'], ObjectWithoutClassType::class => ['object'], NeverType::class => ['never', 'never-return', 'never-returns', 'no-return']];
    public function mapScalarStringToType(string $scalarName) : Type
    {
        $loweredScalarName = Strings::lower($scalarName);
        if ($loweredScalarName === 'false') {
            return new ConstantBooleanType(\false);
        }
        if ($loweredScalarName === 'true') {
            return new ConstantBooleanType(\true);
        }
        if ($loweredScalarName === 'positive-int') {
            return IntegerRangeType::createAllGreaterThan(0);
        }
        if ($loweredScalarName === 'negative-int') {
            return IntegerRangeType::createAllSmallerThan(0);
        }
        foreach (self::SCALAR_NAME_BY_TYPE as $objectType => $scalarNames) {
            if (!\in_array($loweredScalarName, $scalarNames, \true)) {
                continue;
            }
            return new $objectType();
        }
        if ($loweredScalarName === 'list') {
            return TypeCombinator::intersect(new ArrayType(new MixedType(), new MixedType()), new AccessoryArrayListType());
        }
        if ($loweredScalarName === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($loweredScalarName === 'iterable') {
            return new IterableType(new MixedType(), new MixedType());
        }
        if ($loweredScalarName === 'mixed') {
            return new MixedType(\true);
        }
        return new MixedType();
    }
}
