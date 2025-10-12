<?php

declare(strict_types=1);

use Rector\ArgTyper\Rector\Rector\AddParamTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([AddParamTypeRector::class]);
