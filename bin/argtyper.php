<?php

declare(strict_types=1);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
require __DIR__ . '/../vendor/autoload.php';

$containerFactory = new ContainerFactory();
$container = $containerFactory->create();


/** @var Application $application */
$application = $container->make(Application::class);

$exitCode = $application->run(new ArgvInput(), new ConsoleOutput());
exit($exitCode);
