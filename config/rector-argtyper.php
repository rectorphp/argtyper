<?php

declare(strict_types=1);

// to local classes in Rector autoload if not run from the same project
require __DIR__ . '/bin/autoload.php';

use Rector\ArgTyper\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector;
use Rector\ArgTyper\Rector\Rector\Function_\AddFunctionParamTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withBootstrapFiles([
        __DIR__ . '/bin/autoload.php'
    ])
    ->withSkip([
        // run only on source code
        'test',
        'tests',
        'Fixture',
        'test',
        'Tests',
    ])
    ->withRules([
        AddFunctionParamTypeRector::class,
        AddClassMethodParamTypeRector::class,
        // AddParamIterableDocblockTypeRector::class,
    ]);
