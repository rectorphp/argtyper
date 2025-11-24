<?php

declare (strict_types=1);
namespace Argtyper202511;

require_once __DIR__ . '/autoload.php';
$containerFactory = new \Rector\ArgTyper\DependencyInjection\ContainerFactory();
$container = $containerFactory->create();
/** @var \Symfony\Component\Console\Application $application */
$application = $container->make(\Argtyper202511\Symfony\Component\Console\Application::class);
$resultCode = $application->run();
exit($resultCode);
