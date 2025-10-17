<?php

declare(strict_types=1);

use Rector\ArgTyper\Command\ArgTyperCommand;
use Rector\ArgTyper\Helpers\ProjectSourceDirFinder;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

require __DIR__ . '/../vendor/autoload.php';

$argTyperCommand = new ArgTyperCommand(
    new ProjectSourceDirFinder(),
    new SymfonyStyle(new ArrayInput([]), new ConsoleOutput())
);

$resultCode = $argTyperCommand->execute(new ArgvInput(), new ConsoleOutput());

exit($resultCode);
