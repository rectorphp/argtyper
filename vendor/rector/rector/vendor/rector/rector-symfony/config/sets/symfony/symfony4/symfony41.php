<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
# https://github.com/symfony/symfony/blob/master/UPGRADE-4.1.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/symfony41/symfony41-console.php');
    $rectorConfig->import(__DIR__ . '/symfony41/symfony41-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony41/symfony41-workflow.php');
    $rectorConfig->import(__DIR__ . '/symfony41/symfony41-framework-bundle.php');
};
