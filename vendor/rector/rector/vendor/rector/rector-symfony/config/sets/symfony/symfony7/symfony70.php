<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-serializer.php');
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-http-foundation.php');
    $rectorConfig->import(__DIR__ . '/symfony70/symfony70-contracts.php');
};
