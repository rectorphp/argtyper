<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Symfony\Symfony62\Rector\ClassMethod\ClassMethod\ArgumentValueResolverToValueResolverRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/pull/47363
        ArgumentValueResolverToValueResolverRector::class,
    ]);
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46880
        'Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\Cache' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Attribute\\Cache',
        // @see https://github.com/symfony/symfony/pull/47363
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Controller\\ArgumentValueResolverInterface' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Controller\\ValueResolverInterface',
    ]);
};
