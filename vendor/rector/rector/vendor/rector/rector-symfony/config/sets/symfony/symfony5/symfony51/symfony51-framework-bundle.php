<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony51\Rector\ClassMethod\RouteCollectionBuilderToRoutingConfiguratorRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RouteCollectionBuilderToRoutingConfiguratorRector::class]);
    // @see https://github.com/symfony/symfony/pull/36943
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Argtyper202511\Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait', 'configureRoutes', 0, new ObjectType('Argtyper202511\Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator'))]);
};
