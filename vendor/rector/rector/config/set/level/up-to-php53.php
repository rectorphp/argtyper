<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([SetList::PHP_53, SetList::PHP_52]);
};
