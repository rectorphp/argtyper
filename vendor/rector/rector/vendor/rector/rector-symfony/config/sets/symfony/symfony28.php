<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Symfony\Symfony28\Rector\MethodCall\GetToConstructorInjectionRector;
use Rector\Symfony\Symfony28\Rector\StaticCall\ParseFileRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ParseFileRector::class, GetToConstructorInjectionRector::class]);
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [
        // @see https://github.com/symfony/symfony/commit/912fc4de8fd6de1e5397be4a94d39091423e5188
        new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, \true, 'Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL'),
        new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, \false, 'Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_PATH'),
        new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, 'relative', 'Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface::RELATIVE_PATH'),
        new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface', 'generate', 2, 'network', 'Argtyper202511\Symfony\Component\Routing\Generator\UrlGeneratorInterface::NETWORK_PATH'),
    ]);
};
