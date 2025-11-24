<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Command;

use Rector\ArgTyper\Enum\ConfigFilePath;
use Rector\ArgTyper\Helpers\FilesLoader;
use Rector\ArgTyper\Process\ProcessRunner;
use Rector\ArgTyper\ValueObject\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class AddTypesCommand extends Command
{
    public function __construct(
        private readonly SymfonyStyle $symfonyStyle,
        private readonly ProcessRunner $processRunner,
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

        $project = new Project($projectPath);
        $isDebug = (bool) $input->getOption('debug');

        $this->symfonyStyle->writeln(sprintf('<fg=green>Code dirs found in the "%s" project</>', $projectPath));
        $this->symfonyStyle->listing($project->getCodeDirectories());
        $this->symfonyStyle->newLine();

        // 1. Run PHPStan data collection
        $this->runPhpStan($project, $isDebug);

        $this->symfonyStyle->newLine();

        // 2. Run Rector to apply types, not on tests, just source
        // Discover source dirs
        $this->runRector($project, $isDebug);

        $this->removeTemporaryPHPStanJsonFiles();

        return Command::SUCCESS;
    }

    private function runPhpStan(Project $project, bool $isDebug): void
    {
        $this->symfonyStyle->title('1. Running PHPStan to collect data...');

        // Keep paths the same as in the original script
        $commands = [
            'vendor/bin/phpstan',
            'analyse',
            ...$project->getCodeDirectories(),
            '--configuration',
            (string) realpath(__DIR__ . '/../../config/phpstan-collecting-data.neon'),
            '--autoload-file',
            (string) realpath(__DIR__ . '/../../bin/autoload.php'),
        ];

        $this->processRunner->runProcess($commands, $project->getDirectory(), $isDebug);

        $collectedFileItems = FilesLoader::loadJsonl(ConfigFilePath::callLikes());
        $this->symfonyStyle->success(sprintf('Finished! Found %d arg types', count($collectedFileItems)));
    }

    private function runRector(Project $project, bool $isDebug): void
    {
        $this->symfonyStyle->title('2. Running Rector to add types...');

        $command = [
            'vendor/bin/rector',
            'process',
            ...$project->getCodeDirectories(),
            '--config',
            (string) realpath(__DIR__ . '/../../rector/rector-argtyper.php'),
            '--clear-cache',
        ];

        // show output, so we know what exactly has changed
        $rectorOutput = $this->processRunner->runProcess($command, $project->getDirectory(), $isDebug);

        $addedTypesCount = $this->resolveAddedTypesCount($rectorOutput);

        if ($addedTypesCount === 0) {
            $this->symfonyStyle->writeln('<fg=green>No new types added. Is your code that good?</>');
            $this->symfonyStyle->newLine();
            return;
        }

        $this->symfonyStyle->success(sprintf('Finished! We have added %d new types', $addedTypesCount));
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

    private function removeTemporaryPHPStanJsonFiles(): void
    {
        if (file_exists(ConfigFilePath::funcCalls())) {
            unlink(ConfigFilePath::funcCalls());
        }

        if (file_exists(ConfigFilePath::callLikes())) {
            unlink(ConfigFilePath::callLikes());
        }
    }
}
