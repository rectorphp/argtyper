<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig): void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableBooleanType = new UnionType([new NullType(), new BooleanType()]);
    $nullableArrayType = new UnionType([new NullType(), $arrayType]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface', 'isReadable', $nullableBooleanType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface', 'isWritable', $nullableBooleanType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\PropertyInfo\PropertyListExtractorInterface', 'getProperties', $nullableArrayType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface', 'getTypes', $nullableArrayType)]);
};
