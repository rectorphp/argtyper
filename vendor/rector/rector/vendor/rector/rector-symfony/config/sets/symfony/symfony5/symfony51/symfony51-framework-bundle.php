<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony51\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RouteCollectionBuilderToRoutingConfiguratorRector::class]);
    // @see https://github.com/symfony/symfony/pull/36943
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Argtyper202511\Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait', 'configureRoutes', 0, new ObjectType('Argtyper202511\Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator'))]);
};
