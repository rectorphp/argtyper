<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/7.4/UPGRADE-7.4.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/symfony74/symfony74-console.php');
    $rectorConfig->import(__DIR__ . '/symfony74/symfony74-framework-bundle.php');
    $rectorConfig->import(__DIR__ . '/symfony74/symfony74-json-streamer.php');
    $rectorConfig->import(__DIR__ . '/symfony74/symfony74-routing.php');
    $rectorConfig->import(__DIR__ . '/symfony74/symfony74-workflow.php');
};
