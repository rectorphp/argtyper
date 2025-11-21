<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony32/symfony32-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony32/symfony32-http-foundation.php');
};
