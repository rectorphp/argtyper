<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', 0, new MixedType(\true)), new AddParamTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\LoaderInterface', 'supports', 0, new MixedType(\true))]);
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\Loader', 'import', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Definition\\ConfigurationInterface', 'getConfigTreeBuilder', new ObjectType('Argtyper202511\\Symfony\\Component\\Config\\Definition\\Builder\\TreeBuilder')), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\FileLocator', 'locate', new UnionType([new StringType(), $arrayType])), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\FileLocatorInterface', 'locate', new UnionType([new StringType(), $arrayType])), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\FileLoader', 'import', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\Loader', 'import', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\LoaderInterface', 'load', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\LoaderInterface', 'supports', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\Loader\\LoaderInterface', 'getResolver', new ObjectType('Argtyper202511\\Symfony\\Component\\Config\\Loader\\LoaderResolverInterface')), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\ResourceCheckerInterface', 'supports', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Config\\ResourceCheckerInterface', 'isFresh', new BooleanType())]);
};
