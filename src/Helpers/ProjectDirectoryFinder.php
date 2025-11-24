<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Webmozart\Assert\Assert;

final class ProjectDirectoryFinder
{
    /**
     * Directory names commonly used as PHP source directories
     * @var string[]
     */
    private const POSSIBLE_SOURCE_DIRECTORIES = ['src', 'lib', 'app'];

    /**
     * Directory names commonly used as PHP source directories
     * @var string[]
     */
    private const POSSIBLE_CODE_DIRECTORIES = ['src', 'lib', 'app', 'test', 'tests'];

    /**
     * @return string[]
     */
    public function find(string $projectPath): array
    {
        $fileInfos = $this->findDirectoriesInPaths($projectPath, self::POSSIBLE_CODE_DIRECTORIES);

        return $this->mapToDirectoryPaths($fileInfos);
    }

    /**
     * @return string[]
     */
    public function findCodeDirsRelative(string $projectPath): array
    {
        $fileInfos = $this->findDirectoriesInPaths($projectPath, self::POSSIBLE_CODE_DIRECTORIES);

        $relativeDirs = [];
        foreach ($fileInfos as $fileInfo) {
            $relativePath = substr(
                (string) realpath($fileInfo->getRealPath()),
                (string) strlen(realpath($projectPath)) + 1
            );
            $relativeDirs[] = $relativePath;
        }

        sort($relativeDirs);

        return $relativeDirs;
    }

    /**
     * @return string[]
     */
    public function findSource(string $projectPath): array
    {
        $fileInfos = $this->findDirectoriesInPaths($projectPath, self::POSSIBLE_SOURCE_DIRECTORIES);

        return $this->mapToDirectoryPaths($fileInfos);
    }

    /**
     * @param SplFileInfo[] $fileInfos
     * @return string[]
     */
    private function mapToDirectoryPaths(array $fileInfos): array
    {
        $dirs = [];
        foreach ($fileInfos as $fileInfo) {
            $dirs[] = $fileInfo->getPathname();
        }

        Assert::notEmpty($dirs);
        Assert::allString($dirs);

        return $dirs;
    }

    /**
     * @param string[] $desiredDirectoryNames
     * @return SplFileInfo[]
     */
    private function findDirectoriesInPaths(string $projectPath, array $desiredDirectoryNames): array
    {
        Assert::allString($desiredDirectoryNames);

        $finder = (new Finder())
            ->in($projectPath)
            ->directories()
            ->depth('== 0')
            ->name($desiredDirectoryNames);

        /** @var SplFileInfo[] $fileInfos */
        $fileInfos = iterator_to_array($finder->getIterator());
        return $fileInfos;
    }
}
