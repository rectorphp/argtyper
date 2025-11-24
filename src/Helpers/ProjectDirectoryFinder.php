<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Helpers;

use SplFileInfo;
use Argtyper202511\Symfony\Component\Finder\Finder;
use Argtyper202511\Webmozart\Assert\Assert;
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
        $relativeDirs = [];
        foreach ($this->findCodeDirsAbsolute($projectPath) as $absoluteDir) {
            $relativeDirs[] = (string) substr($absoluteDir, strlen((string) realpath($projectPath)) + 1);
        }
        return $relativeDirs;
    }
    /**
     * @return string[]
     */
    public function findCodeDirsAbsolute(string $projectPath): array
    {
        $fileInfos = $this->findDirectoriesInPaths($projectPath, self::POSSIBLE_CODE_DIRECTORIES);
        $absoluteDirs = [];
        foreach ($fileInfos as $fileInfo) {
            $absoluteDirs[] = $fileInfo->getRealPath();
        }
        return $absoluteDirs;
    }
    /**
     * @param string[] $desiredDirectoryNames
     * @return SplFileInfo[]
     */
    private function findDirectoriesInPaths(string $projectPath, array $desiredDirectoryNames): array
    {
        Assert::allString($desiredDirectoryNames);
        $finder = (new Finder())->in($projectPath)->directories()->depth('== 0')->name($desiredDirectoryNames)->sortByName();
        /** @var SplFileInfo[] $fileInfos */
        $fileInfos = iterator_to_array($finder->getIterator());
        return $fileInfos;
    }
}
