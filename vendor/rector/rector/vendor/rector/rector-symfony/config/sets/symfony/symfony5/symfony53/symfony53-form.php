<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Form\DataMapperInterface', 'mapFormsToData', 0, new ObjectType(\Traversable::class)),
        // @see https://github.com/symfony/symfony/commit/ce77be2507631cd12e4ca37510dab37f4c2b759a
        new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Form\DataMapperInterface', 'mapDataToForms', 1, new ObjectType(\Traversable::class)),
    ]);
};
