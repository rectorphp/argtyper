<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // console
        'Argtyper202511\Symfony\Component\Console\Helper\ProgressHelper' => 'Argtyper202511\Symfony\Component\Console\Helper\ProgressBar',
    ]);
};
