<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/7.2/UPGRADE-7.2.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony72/symfony72-serializer.php');
    $rectorConfig->import(__DIR__ . '/symfony72/symfony72-http-foundation.php');
};
