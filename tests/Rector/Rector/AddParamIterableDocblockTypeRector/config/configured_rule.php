<?php

declare(strict_types=1);

use Rector\ArgTyper\Rector\Rector\AddParamIterableDocblockTypeRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withRules([AddParamIterableDocblockTypeRector::class]);
