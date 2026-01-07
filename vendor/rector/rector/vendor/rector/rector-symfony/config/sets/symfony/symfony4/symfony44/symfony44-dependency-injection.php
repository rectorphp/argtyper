<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['Argtyper202601\Symfony\Component\DependencyInjection\Loader\Configurator\tagged' => 'Argtyper202601\Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator']);
};
