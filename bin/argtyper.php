<?php

declare (strict_types=1);
namespace Argtyper202511;

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
$containerFactory = new \Rector\ArgTyper\DependencyInjection\ContainerFactory();
$container = $containerFactory->create();
/** @var \Symfony\Component\Console\Application $application */
$application = $container->make(\Argtyper202511\Symfony\Component\Console\Application::class);
$resultCode = $application->run();
exit($resultCode);
