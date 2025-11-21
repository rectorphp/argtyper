<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/47595
        'Argtyper202511\Symfony\Component\HttpFoundation\ExpressionRequestMatcher' => 'Argtyper202511\Symfony\Component\HttpFoundation\RequestMatcher\ExpressionRequestMatcher',
        'Argtyper202511\Symfony\Component\HttpFoundation\RequestMatcher' => 'Argtyper202511\Symfony\Component\HttpFoundation\ChainRequestMatcher',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/45034
        new MethodCallRename('Argtyper202511\Symfony\Component\HttpFoundation\Request', 'getContentType', 'getContentTypeFormat'),
    ]);
};
