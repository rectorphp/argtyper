<?php

declare (strict_types=1);
namespace Argtyper202511;

use Rector\ArgTyper\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector;
use Rector\ArgTyper\Rector\Rector\Function_\AddFunctionParamTypeRector;
use Rector\Config\RectorConfig;
return RectorConfig::configure()->withBootstrapFiles([__DIR__ . '/vendor/autoload.php'])->withRules([AddFunctionParamTypeRector::class, AddClassMethodParamTypeRector::class]);
