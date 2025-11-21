<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
// https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#mime
        new MethodCallRename('Argtyper202511\Symfony\Component\Mime\Address', 'fromString', 'create'),
    ]);
};
