<?php

declare(strict_types=1);

use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Helpers\ProjectSourceDirFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

require __DIR__ . '/../vendor/autoload.php';

// run on project: bin/argtyper.php project

final class ArgTyper
{
    private ProjectSourceDirFinder $projectSourceDirFinder;

    public function __construct()
    {
        $this->projectSourceDirFinder = new ProjectSourceDirFinder();
    }

    public function process(?string $projectPath): void
    {
        // 1. take 1st arg from console and verify the path exits
        Assert::notNull($projectPath, 'Give path to existing directory as 1st argument');
        Assert::directory($projectPath, 'Give path to existing directory as 1st argument');

        // 2. run phpstan with local config on obvious code directories - use symfony/finder
        $projectDirs = $this->projectSourceDirFinder->find($projectPath);
    }
}

$symfonyStyle = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());

echo 'Found project code directories:' . PHP_EOL;
$symfonyStyle->listing($projectDirs);

$symfonyStyle->title('1. Running PHPStan to collect data...');

$phpstanAnalyzeCommand = sprintf(
    'vendor/bin/phpstan analyse %s --configuration=%s --autoload-file=%s/vendor/autoload.php',
    implode(' ', $projectDirs),
    'phpstan-data-collector.neon',
    $projectPath
);
exec($phpstanAnalyzeCommand);

$collectedFileItems = FilesLoader::loadFileJson(ConfigFilePath::phpstanCollectedData());
$symfonyStyle->success(sprintf('Finished! Found %d arg types', count($collectedFileItems)));

$symfonyStyle->title('2. Running Rector to add types...');

$rectorProcessCommand = sprintf(
    'vendor/bin/rector process %s --config=rector-argtyper.php',
    implode(' ', $projectDirs),
);
exec($rectorProcessCommand);

$symfonyStyle->success('Finished! Now go check the project new types!');

$argTyper = new ArgTyper();
$projectPath = $argv[1] ?? null;
$argTyper->process($projectPath);

exit(Command::SUCCESS);
