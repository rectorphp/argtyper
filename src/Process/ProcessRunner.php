<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Process;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

final class ProcessRunner
{
    public function __construct(
        private SymfonyStyle $symfonyStyle
    ) {
    }

    /**
     * @param string[] $commands
     */
    public function runProcess(array $commands, string $projectPath, bool $isDebug): string
    {
        Assert::allString($commands);

        $process = new Process($commands, cwd: $projectPath);
        $process->setTimeout(null);

        if ($isDebug) {
            $this->symfonyStyle->writeln(sprintf('<info>$ %s</info>', $process->getCommandLine()));
            $this->symfonyStyle->newLine();
        }

        $process->mustRun();
        return $process->getOutput();
    }
}
