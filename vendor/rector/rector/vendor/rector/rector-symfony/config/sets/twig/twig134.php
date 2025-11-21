<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Twig134\Rector\Return_\SimpleFunctionAndFilterRector;
return RectorConfig::configure()->withRules([SimpleFunctionAndFilterRector::class]);
