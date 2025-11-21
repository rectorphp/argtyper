<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/20ab567385e3812ef661dae01a1fdc5d1bde2666
        'Argtyper202511\\Http\\Client\\HttpClient' => 'Argtyper202511\\Psr\\Http\\Client\\ClientInterface',
    ]);
};
