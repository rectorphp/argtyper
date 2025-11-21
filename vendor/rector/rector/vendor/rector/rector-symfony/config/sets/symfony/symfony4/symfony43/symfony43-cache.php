<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/pull/29236
        'Argtyper202511\\Symfony\\Component\\Cache\\Traits\\ApcuTrait\\ApcuCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Traits\\ApcuTrait\\ApcuAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\SimpleCacheAdapter' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\Psr16Adapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\ArrayCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\ArrayAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\ChainCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\ChainAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\DoctrineCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\DoctrineAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\FilesystemCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\FilesystemAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\MemcachedCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\MemcachedAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\NullCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\NullAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\PdoCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\PdoAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\PhpArrayCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\PhpArrayAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\PhpFilesCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\PhpFilesAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\RedisCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\RedisAdapter',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\TraceableCache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Adapter\\TraceableAdapterCache',
        'Argtyper202511\\Symfony\\Component\\Cache\\Simple\\Psr6Cache' => 'Argtyper202511\\Symfony\\Component\\Cache\\Psr16Cache',
    ]);
};
