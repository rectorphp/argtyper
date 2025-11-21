<?php

declare (strict_types=1);
namespace Argtyper202511;

use Rector\ArgTyper\Command\ArgTyperCommand;
use Rector\ArgTyper\Helpers\ProjectSourceDirFinder;
use Argtyper202511\Symfony\Component\Console\Input\ArgvInput;
use Argtyper202511\Symfony\Component\Console\Input\ArrayInput;
use Argtyper202511\Symfony\Component\Console\Output\ConsoleOutput;
use Argtyper202511\Symfony\Component\Console\Style\SymfonyStyle;
// installed as dependency
if (\file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../../../vendor/autoload.php';
}
if (\file_exists(__DIR__ . '/../vendor/scoper-autoload.php')) {
    // A. build downgraded package
    require_once __DIR__ . '/../vendor/scoper-autoload.php';
} else {
    // B. local repository
    require_once __DIR__ . '/../vendor/autoload.php';
}
$argTyperCommand = new ArgTyperCommand(new ProjectSourceDirFinder(), new SymfonyStyle(new ArrayInput([]), new ConsoleOutput()));
$argvInput = new ArgvInput(null, $argTyperCommand->getDefinition());
$resultCode = $argTyperCommand->execute($argvInput, new ConsoleOutput());
exit($resultCode);
