<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/commit/07dd09db59e2f2a86a291d00d978169d9059e307
        'Argtyper202601\Symfony\Bundle\FrameworkBundle\DataCollector\RequestDataCollector' => 'Argtyper202601\Symfony\Component\HttpKernel\DataCollector\RequestDataCollector',
    ]);
};
