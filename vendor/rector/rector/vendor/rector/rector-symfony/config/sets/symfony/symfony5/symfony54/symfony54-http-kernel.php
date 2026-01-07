<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45615
        'Argtyper202601\Symfony\Component\HttpKernel\EventListener\AbstractTestSessionListener' => 'Argtyper202601\Symfony\Component\HttpKernel\EventListener\AbstractSessionListener',
        'Argtyper202601\Symfony\Component\HttpKernel\EventListener\TestSessionListener' => 'Argtyper202601\Symfony\Component\HttpKernel\EventListener\SessionListener',
    ]);
};
