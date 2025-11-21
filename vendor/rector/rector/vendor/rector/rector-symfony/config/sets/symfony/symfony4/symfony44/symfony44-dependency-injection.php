<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['Argtyper202511\Symfony\Component\DependencyInjection\Loader\Configurator\tagged' => 'Argtyper202511\Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator']);
};
