<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

// run on project: bin/argtyper.php project

// take 1st arg from console and verify the path exits
die;

$projectPath = $argv[1] ?? null;
\Webmozart\Assert\Assert::notNull($projectPath, 'Give path to existing directory as 1st argument');
\Webmozart\Assert\Assert::directory($projectPath, 'Give path to existing directory as 1st argument');

// 1. run phpstan with local config on obvious code directories - use symfony/finder

$projectDirFinder = new ProjectDirFinder();
$projectDirs = $projectDirFinder->findProjectCodeDirs($projectPath);

dump($projectDirs);
die;

echo '1. Running PHPStan to collect data...' . PHP_EOL . PHP_EOL;

exec('vendor/bin/phpstan analyse ' . implode(' ', $projectDirs) . ' --configuration=phpstan-data-collector.neon');


final class ProjectDirFinder
{
    /**
     * @return string[]
     */
    public function findProjectCodeDirs(string $projectPath): array
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
        };

        \Webmozart\Assert\Assert::notEmpty($dirs);
        \Webmozart\Assert\Assert::allString($dirs);

        return $dirs;
    }
}


// 2. run rector with local config