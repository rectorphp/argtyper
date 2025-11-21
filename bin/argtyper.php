<?php

declare(strict_types=1);

use Rector\ArgTyper\Command\ArgTyperCommand;
use Rector\ArgTyper\Helpers\ProjectSourceDirFinder;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

// installed as dependency
if (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../../vendor/autoload.php';
}

if (file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    // A. build downgraded package
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
} else {
    // B. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}

$argTyperCommand = new ArgTyperCommand(
    new ProjectSourceDirFinder(),
    new SymfonyStyle(new ArrayInput([]), new ConsoleOutput())
);

$application = new \Symfony\Component\Console\Application();
$application->add($argTyperCommand);

$resultCode = $application->run();
exit($resultCode);
