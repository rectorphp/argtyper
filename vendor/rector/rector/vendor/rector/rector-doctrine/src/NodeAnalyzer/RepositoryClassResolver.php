<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeAnalyzer;

use Argtyper202511\RectorPrefix202511\Nette\Utils\FileSystem;
use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
final class RepositoryClassResolver
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var string
     */
    private const QUOTED_REPOSITORY_CLASS_REGEX = '#repositoryClass=\"(?<repositoryClass>.*?)\"#';
    /**
     * @var string
     */
    private const USE_REPOSITORY_REGEX = '#use (?<repositoryClass>.*?Repository);#';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function resolveFromEntityClass(string $entityClassName): ?string
    {
        if (!$this->reflectionProvider->hasClass($entityClassName)) {
            throw new ShouldNotHappenException();
        }
        $classReflection = $this->reflectionProvider->getClass($entityClassName);
        $entityClassFileName = $classReflection->getFileName();
        if ($entityClassFileName === null) {
            return null;
        }
        $entityFileContents = FileSystem::read($entityClassFileName);
        $repositoryClass = null;
        // match repositoryClass="..." in entity
        $match = Strings::match($entityFileContents, self::QUOTED_REPOSITORY_CLASS_REGEX);
        if (!isset($match['repositoryClass'])) {
            // try fallback to repository ::class + use import
            $repositoryUseMatch = Strings::match($entityFileContents, self::USE_REPOSITORY_REGEX);
            if (isset($repositoryUseMatch['repositoryClass'])) {
                $repositoryClass = $repositoryUseMatch['repositoryClass'];
            }
        } else {
            $repositoryClass = $match['repositoryClass'];
        }
        if ($repositoryClass === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($repositoryClass)) {
            throw new ShouldNotHappenException(sprintf('Repository class "%s" for entity "%s" does not exist', $repositoryClass, $entityClassName));
        }
        return $repositoryClass;
    }
}
