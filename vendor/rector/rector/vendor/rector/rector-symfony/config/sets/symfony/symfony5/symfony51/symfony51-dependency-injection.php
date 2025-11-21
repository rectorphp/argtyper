<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\FuncCall\RenameFunctionRector;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameFunctionRector::class, ['Argtyper202511\\Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\inline' => 'Argtyper202511\\Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\inline_service', 'Argtyper202511\\Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ref' => 'Argtyper202511\\Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\service']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\\Symfony\\Component\\DependencyInjection\\Definition', 'getDeprecationMessage', 'getDeprecation'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\DependencyInjection\\Alias', 'getDeprecationMessage', 'getDeprecation')]);
};
