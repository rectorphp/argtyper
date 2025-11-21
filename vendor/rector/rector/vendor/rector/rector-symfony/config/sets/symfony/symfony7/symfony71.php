<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
// @see https://github.com/symfony/symfony/blob/7.1/UPGRADE-7.1.md
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony71/symfony71-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony71/symfony71-serializer.php');
};
