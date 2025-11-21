<?php

declare(strict_types=1);

use Rector\ArgTyper\Rector\Rector\ClassMethod\AddClassMethodParamTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([AddClassMethodParamTypeRector::class]);
