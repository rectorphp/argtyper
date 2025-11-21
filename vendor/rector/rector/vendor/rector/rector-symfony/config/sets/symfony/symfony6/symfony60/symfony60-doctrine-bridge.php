<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
# https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/40403
        new MethodCallRename('Argtyper202511\Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
    ]);
};
