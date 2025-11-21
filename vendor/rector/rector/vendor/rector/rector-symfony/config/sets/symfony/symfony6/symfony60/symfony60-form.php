<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\PHPStan\Type\VoidType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableStringType = new UnionType([new NullType(), new StringType()]);
    $typeGuessType = new ObjectType('Argtyper202511\\Symfony\\Component\\Form\\Guess\\TypeGuess');
    $nullableValueGuessType = new UnionType([new NullType(), new ObjectType('Argtyper202511\\Symfony\\Component\\Form\\Guess\\ValueGuess')]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\AbstractExtension', 'loadTypes', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\AbstractExtension', 'loadTypeGuesser', new UnionType([new NullType(), new ObjectType('Argtyper202511\\Symfony\\Component\\Form\\FormTypeGuesserInterface')])), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\AbstractRendererEngine', 'loadResourceForBlockName', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\AbstractType', 'getBlockPrefix', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\AbstractType', 'getParent', $nullableStringType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\DataTransformerInterface', 'transform', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\DataTransformerInterface', 'reverseTransform', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormRendererEngineInterface', 'renderBlock', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessType', new UnionType([new NullType(), $typeGuessType])), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessRequired', $nullableValueGuessType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessMaxLength', $nullableValueGuessType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeGuesserInterface', 'guessPattern', $nullableValueGuessType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeInterface', 'getBlockPrefix', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeInterface', 'getParent', $nullableStringType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeInterface', 'buildForm', new VoidType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\FormTypeInterface', 'configureOptions', new VoidType())]);
};
