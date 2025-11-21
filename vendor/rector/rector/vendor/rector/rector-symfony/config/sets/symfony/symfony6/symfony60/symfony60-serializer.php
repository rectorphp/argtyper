<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\ObjectWithoutClassType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Argtyper202511\Rector\Util\Reflection\PrivatesAccessor;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig): void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $scalarTypes = [$arrayType, new BooleanType(), new StringType(), new IntegerType(), new FloatType(), new NullType()];
    $scalarArrayObjectUnionedTypes = \array_merge($scalarTypes, [new ObjectType('ArrayObject')]);
    // cannot be crated with \PHPStan\Type\UnionTypeHelper::sortTypes() as ObjectType requires a class reflection we do not have here
    $unionTypeReflectionClass = new \ReflectionClass(UnionType::class);
    /** @var UnionType $scalarArrayObjectUnionType */
    $scalarArrayObjectUnionType = $unionTypeReflectionClass->newInstanceWithoutConstructor();
    $privatesAccessor = new PrivatesAccessor();
    $privatesAccessor->setPrivateProperty($scalarArrayObjectUnionType, 'types', $scalarArrayObjectUnionedTypes);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Encoder\DecoderInterface', 'decode', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Encoder\DecoderInterface', 'supportsDecoding', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractNormalizer', 'getAllowedAttributes', new UnionType([$arrayType, new BooleanType()])), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractNormalizer', 'isAllowedAttribute', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractNormalizer', 'instantiateObject', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'supportsNormalization', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'instantiateObject', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'extractAttributes', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'getAttributeValue', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'supportsDenormalization', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'denormalize', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\DenormalizerInterface', 'denormalize', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\DenormalizerInterface', 'supportsDenormalization', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\NormalizerInterface', 'supportsNormalization', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer', 'normalize', $scalarArrayObjectUnionType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Serializer\Normalizer\NormalizerInterface', 'normalize', $scalarArrayObjectUnionType)]);
};
