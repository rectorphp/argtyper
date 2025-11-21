<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver', 'setNormalizer', new SimpleStaticType('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver', 'setAllowedValues', new SimpleStaticType('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver', 'addAllowedValues', new SimpleStaticType('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver', 'setAllowedTypes', new SimpleStaticType('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver')), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver', 'addAllowedTypes', new SimpleStaticType('Argtyper202511\Symfony\Component\OptionsResolver\OptionsResolver'))]);
};
