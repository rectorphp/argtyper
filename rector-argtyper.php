<?php

declare(strict_types=1);

use Rector\ArgTyper\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector;
use Rector\ArgTyper\Rector\Rector\Function_\AddFunctionParamTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([
        AddFunctionParamTypeRector::class,
        AddClassMethodParamTypeRector::class,
        // AddParamIterableDocblockTypeRector::class,
    ]);
