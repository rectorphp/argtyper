<?php

declare(strict_types=1);

use Nette\Utils\FileSystem;
use Rector\ArgTyper\Enum\ConfigFilePath;

require __DIR__ . '/../vendor/autoload.php';

// run on project: bin/argtyper.php project
// 1. take 1st arg from console and verify the path exits
$projectPath = $argv[1] ?? null;
\Webmozart\Assert\Assert::notNull($projectPath, 'Give path to existing directory as 1st argument');
\Webmozart\Assert\Assert::directory($projectPath, 'Give path to existing directory as 1st argument');

// 2. run phpstan with local config on obvious code directories - use symfony/finder
$projectDirFinder = new ProjectDirFinder();
$projectDirs = $projectDirFinder->findProjectCodeDirs($projectPath);

echo 'Found project code directories:' . PHP_EOL;
foreach ($projectDirs as $projectDir) {
    echo '- ' . $projectDir . PHP_EOL;
}

echo PHP_EOL;

echo '1. Running PHPStan to collect data...' . PHP_EOL . PHP_EOL;

$command = sprintf(
    'vendor/bin/phpstan analyse ' . implode(
        ' ',
        $projectDirs
    ) . ' --configuration=phpstan-data-collector.neon --autoload-file=%s/vendor/autoload.php',
    $projectPath
);
echo 'Command: ' . $command;
exec($command);

echo 'Finished!' . PHP_EOL . PHP_EOL;

$collectedFileContents = FileSystem::read(ConfigFilePath::phpstanCollectedData());
$collectedFileItems = \Nette\Utils\Json::decode($collectedFileContents);

echo 'Found ' . count($collectedFileItems) . ' type items' . PHP_EOL . PHP_EOL;

$command = sprintf(
    'vendor/bin/rector process ' . implode(' ', $projectDirs) . ' --config=rector-argtyper.php',
    $projectPath
);
echo 'Command: ' . $command;
exec($command);

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
        }

        \Webmozart\Assert\Assert::notEmpty($dirs);
        \Webmozart\Assert\Assert::allString($dirs);

        return $dirs;
    }
}

// 2. run rector with local config
