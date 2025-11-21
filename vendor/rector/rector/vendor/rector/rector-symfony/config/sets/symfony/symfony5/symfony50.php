<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/5.0/UPGRADE-5.0.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony50/symfony50-types.php');
    $rectorConfig->import(__DIR__ . '/symfony50/symfony50-console.php');
    $rectorConfig->import(__DIR__ . '/symfony50/symfony50-debug.php');
};
