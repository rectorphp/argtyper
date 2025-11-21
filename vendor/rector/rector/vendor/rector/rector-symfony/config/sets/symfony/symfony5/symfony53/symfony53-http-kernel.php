<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassConstFetch;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Event\KernelEvent', 'isMasterRequest', 'isMainRequest')]);
    // rename constant
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        // @see https://github.com/symfony/symfony/pull/40536
        new RenameClassConstFetch('Argtyper202511\Symfony\Component\HttpKernel\HttpKernelInterface', 'MASTER_REQUEST', 'MAIN_REQUEST'),
    ]);
};
