<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Process;

use Argtyper202511\Symfony\Component\Console\Style\SymfonyStyle;
use Argtyper202511\Symfony\Component\Process\Process;
use Argtyper202511\Webmozart\Assert\Assert;
final class ProcessRunner
{
    /**
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @param string[] $commands
     */
    public function runProcess(array $commands, string $projectPath, bool $isDebug): string
    {
        Assert::allString($commands);
        $process = new Process($commands, $projectPath);
        $process->setTimeout(null);
        if ($isDebug) {
            $this->symfonyStyle->writeln(sprintf('<info>$ %s</info>', $process->getCommandLine()));
            $this->symfonyStyle->newLine();
        }
        $process->mustRun();
        return $process->getOutput();
    }
}
