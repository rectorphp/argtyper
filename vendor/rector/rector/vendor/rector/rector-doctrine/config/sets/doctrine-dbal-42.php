<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\Dbal42\Rector\New_\AddArrayResultColumnNamesRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/doctrine/dbal/pull/6504/files
        AddArrayResultColumnNamesRector::class,
    ]);
};
