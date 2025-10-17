<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Webmozart\Assert\Assert;

final class ProjectSourceDirFinder
{
    /**
     * Directory names commonly used as PHP source directories
     * @var string[]
     */
    private const POSSIBLE_SOURCE_DIRECTORIES = ['src', 'lib', 'app', 'tests'];

    /**
     * @return string[]
     */
    public function find(string $projectPath): array
    {
        $finder = (new Finder())
            ->in($projectPath)
            ->directories()
            ->depth('== 0')
            ->name(self::POSSIBLE_SOURCE_DIRECTORIES);

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
