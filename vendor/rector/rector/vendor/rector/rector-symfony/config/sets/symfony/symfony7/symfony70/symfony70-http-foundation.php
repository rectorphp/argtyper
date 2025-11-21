<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#httpfoundation
        'Argtyper202511\Symfony\Component\HttpFoundation\RequestMatcher' => 'Argtyper202511\Symfony\Component\HttpFoundation\ChainRequestMatcher',
        'Argtyper202511\Symfony\Component\HttpFoundation\ExpressionRequestMatcher' => 'Argtyper202511\Symfony\Component\HttpFoundation\RequestMatcher\ExpressionRequestMatcher',
    ]);
    // @see https://github.com/symfony/symfony/pull/50826
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\HttpFoundation\Request', 'getContentType', 'getContentTypeFormat')]);
};
