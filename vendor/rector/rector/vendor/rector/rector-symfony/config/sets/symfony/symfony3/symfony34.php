<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->import(__DIR__ . '/symfony34/symfony34-yaml.php');
    $rectorConfig->import(__DIR__ . '/symfony34/symfony34-dependency-injection.php');
    $rectorConfig->import(__DIR__ . '/symfony34/symfony34-sensio-framework-extra-bundle.php');
};
