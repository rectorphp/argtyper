<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/symfony/symfony/pull/29236
        'Argtyper202601\Symfony\Component\Cache\Traits\ApcuTrait\ApcuCache' => 'Argtyper202601\Symfony\Component\Cache\Traits\ApcuTrait\ApcuAdapter',
        'Argtyper202601\Symfony\Component\Cache\Adapter\SimpleCacheAdapter' => 'Argtyper202601\Symfony\Component\Cache\Adapter\Psr16Adapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\ArrayCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\ArrayAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\ChainCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\ChainAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\DoctrineCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\DoctrineAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\FilesystemCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\FilesystemAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\MemcachedCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\MemcachedAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\NullCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\NullAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\PdoCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\PdoAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\PhpArrayCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\PhpArrayAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\PhpFilesCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\PhpFilesAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\RedisCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\RedisAdapter',
        'Argtyper202601\Symfony\Component\Cache\Simple\TraceableCache' => 'Argtyper202601\Symfony\Component\Cache\Adapter\TraceableAdapterCache',
        'Argtyper202601\Symfony\Component\Cache\Simple\Psr6Cache' => 'Argtyper202601\Symfony\Component\Cache\Psr16Cache',
    ]);
};
