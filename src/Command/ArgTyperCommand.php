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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

final class ArgTyperCommand extends Command
{
    public function __construct(
        private readonly ProjectSourceDirFinder $projectSourceDirFinder,
        private readonly SymfonyStyle $symfonyStyle,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('argtyper');

        $this->addArgument(
            'project-path',
            InputArgument::REQUIRED,
            'Path to the target project root'
        );

        $this->addOption('debug', null, null, 'Enable debug output');
    }

    /**
     * @return Command::*
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $projectPath = (string) $input->getArgument('project-path');

        // Validate input
        Assert::notEmpty($projectPath, 'Give path to existing directory as 1st argument');
        Assert::directory($projectPath, 'Give path to existing directory as 1st argument');

        // Discover source dirs
        $projectDirs = $this->projectSourceDirFinder->find($projectPath);

        $isDebug = (bool) $input->getOption('debug');

        $this->symfonyStyle->writeln('Found project code directories');
        $this->symfonyStyle->listing($projectDirs);

        // 1. Run PHPStan data collection
        $this->runPhpStan($projectDirs, $projectPath, $isDebug);

        // 2. Run Rector to apply types
        $this->runRector($projectDirs, $isDebug);

        return Command::SUCCESS;
    }

    /**
     * @param string[] $projectDirs
     */
    private function runPhpStan(array $projectDirs, string $projectPath, bool $isDebug): void
    {
        $this->symfonyStyle->title('1. Running PHPStan to collect data...');

        $dirs = array_map(static fn (string $d) => escapeshellarg($d), $projectDirs);

        // Keep paths the same as in the original script
        $cmd = sprintf(
            'vendor/bin/phpstan analyse %s --configuration=%s --autoload-file=%s',
            implode(' ', $dirs),
            escapeshellarg('phpstan-data-collector.neon'),
            escapeshellarg($projectPath . '/vendor/autoload.php')
        );

        $this->runShell($cmd, $isDebug);

        // @todo consdier jsonln
        $collectedFileItems = FilesLoader::loadFileJson(ConfigFilePath::callLikes());

        $this->symfonyStyle->newLine();
        $this->symfonyStyle->success(sprintf('Finished! Found %d arg types', count($collectedFileItems)));
    }

    /**
     * @param string[] $projectDirs
     */
    private function runRector(array $projectDirs, bool $isDebug): void
    {
        $this->symfonyStyle->title('2. Running Rector to add types...');

        $dirs = array_map(static fn (string $d) => escapeshellarg($d), $projectDirs);

        $cmd = sprintf(
            'vendor/bin/rector process %s --config=%s',
            implode(' ', $dirs),
            escapeshellarg('rector-argtyper.php')
        );

        if ($isDebug) {
            $this->symfonyStyle->note($cmd);
        }
        $this->runShell($cmd, $isDebug);


        $this->symfonyStyle->newLine();
        $this->symfonyStyle->success('Finished! Now go check the project new types!');
    }

    private function runShell(string $commandLine, bool $isDebug): void
    {
        // Use /bin/sh -lc to allow shell features in the composed commandline
        $process = Process::fromShellCommandline($commandLine);
        $process->setTimeout(null);

        if ($isDebug) {
            $this->symfonyStyle->writeln(sprintf('<info>$ %s</info>', $commandLine));
        }

        $process->run(function ($type, $buffer) {
            $this->symfonyStyle->write($buffer);
        });

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
