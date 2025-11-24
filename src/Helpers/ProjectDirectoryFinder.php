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
    private const POSSIBLE_CODE_DIRECTORIES = ['src', 'lib', 'app', 'test', 'tests'];

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
                strlen((string) realpath($projectPath)) + 1
            );
            $relativeDirs[] = $relativePath;
        }

        sort($relativeDirs);

        return $relativeDirs;
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
