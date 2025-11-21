<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig): void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $nullableStringType = new UnionType([new NullType(), new StringType()]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Validator\Constraint', 'getDefaultOption', $nullableStringType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Validator\Constraint', 'getRequiredOptions', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Validator\Constraint', 'validatedBy', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Validator\Constraint', 'getTargets', new UnionType([new StringType(), $arrayType]))]);
};
