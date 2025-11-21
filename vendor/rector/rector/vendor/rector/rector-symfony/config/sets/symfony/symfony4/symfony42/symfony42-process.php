<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony42\Rector\New_\StringToArrayArgumentProcessRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // https://symfony.com/blog/new-in-symfony-4-2-important-deprecations
        StringToArrayArgumentProcessRector::class,
    ]);
};
