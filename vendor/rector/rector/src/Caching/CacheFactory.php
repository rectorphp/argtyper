<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Caching;

use Argtyper202511\Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Argtyper202511\Rector\Caching\ValueObject\Storage\MemoryCacheStorage;
use Argtyper202511\Rector\Configuration\Option;
use Argtyper202511\Rector\Configuration\Parameter\SimpleParameterProvider;
use Argtyper202511\RectorPrefix202511\Symfony\Component\Filesystem\Filesystem;
final class CacheFactory
{
    /**
     * @readonly
     * @var \RectorPrefix202511\Symfony\Component\Filesystem\Filesystem
     */
    private $fileSystem;
    public function __construct(Filesystem $fileSystem)
    {
        $this->fileSystem = $fileSystem;
    }
    /**
     * @api config factory
     */
    public function create(): \Argtyper202511\Rector\Caching\Cache
    {
        $cacheDirectory = SimpleParameterProvider::provideStringParameter(Option::CACHE_DIR);
        $cacheClass = FileCacheStorage::class;
        if (SimpleParameterProvider::hasParameter(Option::CACHE_CLASS)) {
            $cacheClass = SimpleParameterProvider::provideStringParameter(Option::CACHE_CLASS);
        }
        if ($cacheClass === FileCacheStorage::class) {
            // ensure cache directory exists
            if (!$this->fileSystem->exists($cacheDirectory)) {
                $this->fileSystem->mkdir($cacheDirectory);
            }
            $fileCacheStorage = new FileCacheStorage($cacheDirectory, $this->fileSystem);
            return new \Argtyper202511\Rector\Caching\Cache($fileCacheStorage);
        }
        return new \Argtyper202511\Rector\Caching\Cache(new MemoryCacheStorage());
    }
}
