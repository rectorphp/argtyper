<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\PHPUnit\PHPUnit100\Rector\StmtsAwareInterface\WithConsecutiveRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit90\Rector\Class_\TestListenerToHooksRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit90\Rector\MethodCall\ExplicitPhpErrorApiRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit90\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([TestListenerToHooksRector::class, ExplicitPhpErrorApiRector::class, SpecificAssertContainsWithoutIdentityRector::class, WithConsecutiveRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // see https://github.com/sebastianbergmann/phpunit/issues/3957
        new MethodCallRename('Argtyper202511\PHPUnit\Framework\TestCase', 'expectExceptionMessageRegExp', 'expectExceptionMessageMatches'),
    ]);
};
