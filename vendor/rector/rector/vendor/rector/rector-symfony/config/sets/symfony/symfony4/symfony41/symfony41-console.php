<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('Argtyper202511\\Symfony\\Component\\Console\\Helper\\TableStyle', 'setHorizontalBorderChar', 'setHorizontalBorderChars'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('Argtyper202511\\Symfony\\Component\\Console\\Helper\\TableStyle', 'setVerticalBorderChar', 'setVerticalBorderChars'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRename('Argtyper202511\\Symfony\\Component\\Console\\Helper\\TableStyle', 'setCrossingChar', 'setDefaultCrossingChar'),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRenameWithArrayKey(
            'Argtyper202511\\Symfony\\Component\\Console\\Helper\\TableStyle',
            'getVerticalBorderChar',
            # special case to "getVerticalBorderChar" → "getBorderChars()[3]"
            'getBorderChars',
            3
        ),
        # https://github.com/symfony/symfony/commit/463f986c28a497571967e37c1314e9911f1ef6ba
        new MethodCallRenameWithArrayKey('Argtyper202511\\Symfony\\Component\\Console\\Helper\\TableStyle', 'getHorizontalBorderChar', 'getBorderChars', 2),
    ]);
};
