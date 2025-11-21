<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/blob/7.1/UPGRADE-7.1.md#dependencyinjection
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // typo fix
        new MethodCallRename('Argtyper202511\Symfony\Component\Serializer\Context\Normalizer\AbstractNormalizerContextBuilder', 'withDefaultContructorArguments', 'withDefaultConstructorArguments'),
    ]);
};
