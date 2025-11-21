<?php

declare (strict_types=1);
namespace Argtyper202511;

use Rector\ArgTyper\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector;
use Rector\ArgTyper\Rector\Rector\Function_\AddFunctionParamTypeRector;
use Argtyper202511\Rector\Config\RectorConfig;
return RectorConfig::configure()->withRules([AddFunctionParamTypeRector::class, AddClassMethodParamTypeRector::class]);
