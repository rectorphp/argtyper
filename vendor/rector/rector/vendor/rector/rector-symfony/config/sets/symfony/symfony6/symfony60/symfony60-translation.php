<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\IterableType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'canBeExtracted', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Translation\\Extractor\\AbstractFileExtractor', 'extractFromDirectory', $iterableType)]);
};
