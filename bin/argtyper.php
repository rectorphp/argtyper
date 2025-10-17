<?php

declare(strict_types=1);

use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Helpers\ProjectSourceDirFinder;

require __DIR__ . '/../vendor/autoload.php';

// run on project: bin/argtyper.php project
// 1. take 1st arg from console and verify the path exits
$projectPath = $argv[1] ?? null;
\Webmozart\Assert\Assert::notNull($projectPath, 'Give path to existing directory as 1st argument');
\Webmozart\Assert\Assert::directory($projectPath, 'Give path to existing directory as 1st argument');

// 2. run phpstan with local config on obvious code directories - use symfony/finder
$projectSourceDirFinder = new ProjectSourceDirFinder();
$projectDirs = $projectSourceDirFinder->find($projectPath);

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
exec($command);

echo 'Finished!' . PHP_EOL . PHP_EOL;

$collectedFileItems = FilesLoader::loadFileJson(ConfigFilePath::phpstanCollectedData());
echo 'Found ' . count($collectedFileItems) . ' type items' . PHP_EOL . PHP_EOL;

echo '2. Running Rector to add types...' . PHP_EOL . PHP_EOL;

$command = sprintf(
    'vendor/bin/rector process ' . implode(' ', $projectDirs) . ' --config=rector-argtyper.php',
    $projectPath
);
//echo 'Command: ' . $command;
exec($command);

echo PHP_EOL;
echo 'Finished! Now go check the project types!' . PHP_EOL;
