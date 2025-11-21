<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // console
        'Argtyper202511\\Symfony\\Component\\Console\\Helper\\ProgressHelper' => 'Argtyper202511\\Symfony\\Component\\Console\\Helper\\ProgressBar',
    ]);
};
