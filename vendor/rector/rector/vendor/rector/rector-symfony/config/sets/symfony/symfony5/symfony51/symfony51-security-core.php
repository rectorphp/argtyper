<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\String_\RenameStringRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameStringRector::class, [
        // @see https://github.com/symfony/symfony/pull/35858
        'ROLE_PREVIOUS_ADMIN' => 'IS_IMPERSONATOR',
    ]);
};
