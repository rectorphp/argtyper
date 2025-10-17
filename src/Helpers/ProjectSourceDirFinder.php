<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use SplFileInfo;
use Webmozart\Assert\Assert;

final class ProjectSourceDirFinder
{
    /**
     * @return string[]
     */
    public function find(string $projectPath): array
    {
        // find directories: src, lib, app, tests
        $possibleDirectories = ['src', 'lib', 'app', 'tests'];

        $finder = (new \Symfony\Component\Finder\Finder())
            ->in($projectPath)
            ->directories()
            ->depth('== 0')
            ->name($possibleDirectories);

        /** @var SplFileInfo[] $fileInfos */
        $fileInfos = iterator_to_array($finder->getIterator());

        $dirs = [];
        foreach ($fileInfos as $fileInfo) {
            $dirs[] = $fileInfo->getPathname();
        }

        Assert::notEmpty($dirs);
        Assert::allString($dirs);

        return $dirs;
    }
}
