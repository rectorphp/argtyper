<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45615
        'Argtyper202511\Symfony\Component\HttpKernel\EventListener\AbstractTestSessionListener' => 'Argtyper202511\Symfony\Component\HttpKernel\EventListener\AbstractSessionListener',
        'Argtyper202511\Symfony\Component\HttpKernel\EventListener\TestSessionListener' => 'Argtyper202511\Symfony\Component\HttpKernel\EventListener\SessionListener',
    ]);
};
