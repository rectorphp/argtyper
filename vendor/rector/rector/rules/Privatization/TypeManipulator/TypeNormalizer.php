<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Privatization\TypeManipulator;

use Argtyper202511\PHPStan\Type\Accessory\AccessoryLiteralStringType;
use Argtyper202511\PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use Argtyper202511\PHPStan\Type\Accessory\AccessoryNonFalsyStringType;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\Constant\ConstantArrayType;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\Constant\ConstantFloatType;
use Argtyper202511\PHPStan\Type\Constant\ConstantIntegerType;
use Argtyper202511\PHPStan\Type\Constant\ConstantStringType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NeverType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeTraverser;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\TypeHasher;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class TypeNormalizer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Privatization\TypeManipulator\ArrayTypeLeastCommonDenominatorResolver
     */
    private $arrayTypeLeastCommonDenominatorResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\TypeHasher
     */
    private $typeHasher;
    /**
     * @var int
     */
    private const MAX_PRINTED_UNION_DOC_LENGHT = 77;
    public function __construct(TypeFactory $typeFactory, StaticTypeMapper $staticTypeMapper, \Argtyper202511\Rector\Privatization\TypeManipulator\ArrayTypeLeastCommonDenominatorResolver $arrayTypeLeastCommonDenominatorResolver, TypeHasher $typeHasher)
    {
        $this->typeFactory = $typeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->arrayTypeLeastCommonDenominatorResolver = $arrayTypeLeastCommonDenominatorResolver;
        $this->typeHasher = $typeHasher;
    }
    /**
     * Generalize false/true constantArrayType to bool,
     * as mostly default value but accepts both
     */
    public function generalizeConstantTypes(Type $type): Type
    {
        $deep = 0;
        return TypeTraverser::map($type, function (Type $type, callable $traverseCallback) use (&$deep): Type {
            ++$deep;
            if ($type instanceof AccessoryNonFalsyStringType || $type instanceof AccessoryLiteralStringType || $type instanceof AccessoryNonEmptyStringType) {
                return new StringType();
            }
            if ($type instanceof ConstantBooleanType) {
                return new BooleanType();
            }
            if ($type instanceof ConstantStringType) {
                return new StringType();
            }
            if ($type instanceof ConstantFloatType) {
                return new FloatType();
            }
            if ($type instanceof ConstantIntegerType) {
                return new IntegerType();
            }
            if ($type instanceof ConstantArrayType) {
                // is relevant int constantArrayType?
                if ($this->isImplicitNumberedListKeyType($type)) {
                    $keyType = $deep === 1 ? new MixedType() : new IntegerType();
                } else {
                    $keyType = $this->generalizeConstantTypes($type->getKeyType());
                }
                // should be string[]
                $itemType = $traverseCallback($type->getItemType(), $traverseCallback);
                if ($itemType instanceof ConstantStringType) {
                    $itemType = new StringType();
                }
                if ($itemType instanceof ConstantArrayType) {
                    $itemType = $this->generalizeConstantTypes($itemType);
                }
                return new ArrayType($keyType, $itemType);
            }
            if ($type instanceof UnionType) {
                $generalizedUnionedTypes = [];
                foreach ($type->getTypes() as $unionedType) {
                    $generalizedUnionedType = $this->generalizeConstantTypes($unionedType);
                    if ($generalizedUnionedType instanceof ArrayType) {
                        $keyType = $this->typeHasher->createTypeHash($generalizedUnionedType->getKeyType());
                        foreach ($generalizedUnionedTypes as $key => $existingUnionedType) {
                            if (!$existingUnionedType instanceof ArrayType) {
                                continue;
                            }
                            $existingKeyType = $this->typeHasher->createTypeHash($existingUnionedType->getKeyType());
                            if ($keyType !== $existingKeyType) {
                                continue;
                            }
                            $uniqueTypes = $this->typeFactory->uniquateTypes([$existingUnionedType->getItemType(), $generalizedUnionedType->getItemType()]);
                            if (count($uniqueTypes) !== 1) {
                                continue;
                            }
                            $generalizedUnionedTypes[$key] = new ArrayType($existingUnionedType->getKeyType(), $uniqueTypes[0]);
                            continue 2;
                        }
                    }
                    $generalizedUnionedTypes[] = $generalizedUnionedType;
                }
                $uniqueGeneralizedUnionTypes = $this->typeFactory->uniquateTypes($generalizedUnionedTypes);
                if (count($uniqueGeneralizedUnionTypes) > 1) {
                    $generalizedUnionType = new UnionType($uniqueGeneralizedUnionTypes);
                    // avoid too huge print in docblock
                    $unionedDocType = $this->staticTypeMapper->mapPHPStanTypeToPHPStanPhpDocTypeNode($generalizedUnionType);
                    // too long
                    if (strlen((string) $unionedDocType) > self::MAX_PRINTED_UNION_DOC_LENGHT && $this->avoidPrintedDocblockTrimming($generalizedUnionType) === \false) {
                        $alwaysKnownArrayType = $this->narrowToAlwaysKnownArrayType($generalizedUnionType);
                        if ($alwaysKnownArrayType instanceof ArrayType) {
                            return $alwaysKnownArrayType;
                        }
                        return new MixedType();
                    }
                    return $generalizedUnionType;
                }
                return $uniqueGeneralizedUnionTypes[0];
            }
            $convertedType = $traverseCallback($type, $traverseCallback);
            if ($convertedType instanceof NeverType) {
                return new MixedType();
            }
            return $convertedType;
        });
    }
    private function isImplicitNumberedListKeyType(ConstantArrayType $constantArrayType): bool
    {
        if (!$constantArrayType->getKeyType() instanceof UnionType) {
            return \false;
        }
        foreach ($constantArrayType->getKeyType()->getTypes() as $key => $keyType) {
            if ($keyType instanceof ConstantIntegerType) {
                if ($keyType->getValue() === $key) {
                    continue;
                }
                return \false;
            }
            return \false;
        }
        return \true;
    }
    private function narrowToAlwaysKnownArrayType(UnionType $unionType): ?ArrayType
    {
        // always an array?
        if (count($unionType->getArrays()) !== count($unionType->getTypes())) {
            return null;
        }
        $arrayUniqueKeyType = $this->arrayTypeLeastCommonDenominatorResolver->sharedArrayStructure(...$unionType->getTypes());
        return new ArrayType($arrayUniqueKeyType, new MixedType());
    }
    /**
     * Is object only? avoid trimming, as auto import handles it better
     */
    private function avoidPrintedDocblockTrimming(UnionType $unionType): bool
    {
        if ($unionType->getConstantScalarTypes() !== []) {
            return \false;
        }
        if ($unionType->getConstantArrays() !== []) {
            return \false;
        }
        return $unionType->getObjectClassNames() !== [];
    }
}
