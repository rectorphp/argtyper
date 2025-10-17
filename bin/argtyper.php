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
//
//final class ArgTyper
//{
//    private ProjectSourceDirFinder $projectSourceDirFinder;
//
//    private SymfonyStyle $symfonyStyle;
//
//    public function __construct()
//    {
//        $this->symfonyStyle = new SymfonyStyle(new ArrayInput([]), new ConsoleOutput());
//        $this->projectSourceDirFinder = new ProjectSourceDirFinder();
//    }
//
//    public function process(?string $projectPath): void
//    {
//        $projectDirs = $this->findProjectDirs($projectPath);
//
//        $this->symfonyStyle->writeln('Found project code directories');
//        $this->symfonyStyle->listing($projectDirs);
//
//        Assert::string($projectPath);
//
//        $this->executePHPStan($projectDirs, $projectPath);
//
//        $this->executeRector($projectDirs);
//    }
//
//    /**
//     * @return string[]
//     */
//    private function findProjectDirs(?string $projectPath): array
//    {
//        // 1. take 1st arg from console and verify the path exits
//        Assert::notNull($projectPath, 'Give path to existing directory as 1st argument');
//        Assert::directory($projectPath, 'Give path to existing directory as 1st argument');
//
//        return $this->projectSourceDirFinder->find($projectPath);
//    }
//
//    /**
//     * @param string[] $projectDirs
//     */
//    private function executePHPStan(array $projectDirs, string $projectPath): void
//    {
//        $this->symfonyStyle->title('1. Running PHPStan to collect data...');
//
//        $phpstanAnalyzeCommand = sprintf(
//            'vendor/bin/phpstan analyse %s --configuration=%s --autoload-file=%s/vendor/autoload.php',
//            implode(' ', $projectDirs),
//            'phpstan-data-collector.neon',
//            $projectPath
//        );
//        exec($phpstanAnalyzeCommand);
//
//        $collectedFileItems = FilesLoader::loadFileJson(ConfigFilePath::callLikes());
//
//        $this->symfonyStyle->newLine();
//
//        $this->symfonyStyle->success(sprintf('Finished! Found %d arg types', count($collectedFileItems)));
//    }
//
//    /**
//     * @param string[] $projectDirs
//     */
//    private function executeRector(array $projectDirs): void
//    {
//        $this->symfonyStyle->title('2. Running Rector to add types...');
//
//        $rectorProcessCommand = sprintf(
//            'vendor/bin/rector process %s --config=rector-argtyper.php',
//            implode(' ', $projectDirs),
//        );
//        exec($rectorProcessCommand);
//
//        $this->symfonyStyle->newLine();
//
//        $this->symfonyStyle->success('Finished! Now go check the project new types!');
//    }
//}

$argTyperCommand = new \Rector\ArgTyper\Command\ArgTyperCommand(
    new ProjectSourceDirFinder(),
    new SymfonyStyle(new ArrayInput([]), new ConsoleOutput())
);

$resultCode = $argTyperCommand->execute(new \Symfony\Component\Console\Input\ArgvInput(), new ConsoleOutput());

//$argTyper = new ArgTyper();
//$projectPath = $argv[1] ?? null;
//
//if ($projectPath === null) {
//     print help
//    echo 'Usage: vendor/bin/argtyper /path/to/your/project' . PHP_EOL;
//} else {
//    $argTyper->process($projectPath);
//}

exit($resultCode);
