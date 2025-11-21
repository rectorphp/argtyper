<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig): void {
    $routeCollectionType = new ObjectType('Argtyper202511\Symfony\Component\Routing\RouteCollection');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Routing\Loader\AnnotationClassLoader', 'getDefaultRouteName', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Routing\Router', 'getRouteCollection', $routeCollectionType), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\Routing\RouterInterface', 'getRouteCollection', $routeCollectionType)]);
};
