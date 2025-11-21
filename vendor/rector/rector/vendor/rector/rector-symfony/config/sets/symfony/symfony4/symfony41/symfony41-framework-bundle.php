<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/commit/07dd09db59e2f2a86a291d00d978169d9059e307
        'Argtyper202511\\Symfony\\Bundle\\FrameworkBundle\\DataCollector\\RequestDataCollector' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\DataCollector\\RequestDataCollector',
    ]);
};
