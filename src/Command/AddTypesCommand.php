<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Command;

use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Helpers\ProjectSourceDirFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

final class AddTypesCommand extends Command
{
    public function __construct(
        private readonly ProjectSourceDirFinder $projectSourceDirFinder,
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('add-types');
        $this->setDescription(
            'Find all passed values and their types to your local methods/functions calls, then add them as type declarations'
        );

        $this->addArgument('project-path', InputArgument::OPTIONAL, 'Path to the target project root', getcwd());

        $this->addOption('debug', null, null, 'Enable debug output');
    }

    /**
     * @return Command::*
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectPath = (string) $input->getArgument('project-path');

        // Validate input
        Assert::notEmpty($projectPath, 'Give path to existing directory as 1st argument');
        Assert::directory(
            $projectPath,
            sprintf('Path "%s" must be existing directory (root project "." or another)', $projectPath)
        );

        // Discover source dirs
        $sourceDirs = $this->projectSourceDirFinder->find($projectPath);

        $isDebug = (bool) $input->getOption('debug');

        $this->symfonyStyle->writeln('<fg=green>Found project code directories</>');
        $this->symfonyStyle->newLine();
        $this->symfonyStyle->listing($sourceDirs);

        // 1. Run PHPStan data collection
        $this->runPhpStan($sourceDirs, $projectPath, $isDebug);

        $this->symfonyStyle->newLine();

        // 2. Run Rector to apply types
        $this->runRector($sourceDirs, $isDebug);

        $this->removeTempFiles();

        return Command::SUCCESS;
    }

    /**
     * @param string[] $projectDirs
     */
    private function runPhpStan(array $projectDirs, string $projectPath, bool $isDebug): void
    {
        $this->symfonyStyle->title('1. Running PHPStan to collect data...');

        $dirs = array_map(escapeshellarg(...), $projectDirs);

        // Keep paths the same as in the original script
        $cmd = sprintf(
            'vendor/bin/phpstan analyse %s --configuration=%s --autoload-file=%s',
            implode(' ', $dirs),
            realpath(__DIR__ . '/../../phpstan-data-collector.neon'),
            escapeshellarg($projectPath . '/vendor/autoload.php')
        );

        $this->runShell($cmd, $isDebug);

        $collectedFileItems = FilesLoader::loadJsonl(ConfigFilePath::callLikes());
        $this->symfonyStyle->success(sprintf('Finished! Found %d arg types', count($collectedFileItems)));
    }

    /**
     * @param string[] $projectDirs
     */
    private function runRector(array $projectDirs, bool $isDebug): void
    {
        $this->symfonyStyle->title('2. Running Rector to add types...');

        $cmd = sprintf(
            'vendor/bin/rector process %s --config=%s --clear-cache',
            implode(' ', $projectDirs),
            realpath(__DIR__ . '/../../rector-argtyper.php'),
        );

        // show output, so we know what exactly has changed
        $rectorOutput = $this->runShell($cmd, $isDebug);
        $addedTypesCount = $this->resolveAddedTypesCount($rectorOutput);

        if ($addedTypesCount === 0) {
            $this->symfonyStyle->writeln('<fg=green>No new types added. Is your code that good?</>');
            $this->symfonyStyle->newLine();
            return;
        }

        $this->symfonyStyle->success(sprintf('Finished! We have added %d new types', $addedTypesCount));
    }

    private function runShell(string $commandLine, bool $isDebug): string
    {
        $process = Process::fromShellCommandline($commandLine);
        $process->setTimeout(null);

        if ($isDebug) {
            $this->symfonyStyle->writeln(sprintf('<info>$ %s</info>', $commandLine));
            $this->symfonyStyle->newLine();
        }

        $process->mustRun();
        return $process->getOutput();
    }

    private function resolveAddedTypesCount(string $rectorOutput): int
    {
        // regex: match lines that start with + but not +++ or @@
        $pattern = '/^(?:\+)(?!\+\+|@@).+/m';

        if (preg_match_all($pattern, $rectorOutput, $matches)) {
            return count($matches[0]);
        }

        return 0;
    }

    private function removeTempFiles(): void
    {
        if (file_exists(ConfigFilePath::funcCalls())) {
            unlink(ConfigFilePath::funcCalls());
        }

        if (file_exists(ConfigFilePath::callLikes())) {
            unlink(ConfigFilePath::callLikes());
        }
    }
}
