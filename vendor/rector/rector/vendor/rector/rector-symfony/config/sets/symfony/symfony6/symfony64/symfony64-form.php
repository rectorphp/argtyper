<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// @see https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\DataTransformerInterface', 'transform', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\DataTransformerInterface', 'reverseTransform', new MixedType())]);
};
