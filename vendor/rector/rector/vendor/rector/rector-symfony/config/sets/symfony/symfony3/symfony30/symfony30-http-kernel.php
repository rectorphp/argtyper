<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\LoggerInterface', 'emerg', 'emergency'), new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\LoggerInterface', 'crit', 'critical'), new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\LoggerInterface', 'err', 'error'), new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\LoggerInterface', 'warn', 'warning'), new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\NullLogger', 'emerg', 'emergency'), new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\NullLogger', 'crit', 'critical'), new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\NullLogger', 'err', 'error'), new MethodCallRename('Argtyper202511\Symfony\Component\HttpKernel\Log\NullLogger', 'warn', 'warning')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\HttpKernel\Debug\ErrorHandler' => 'Argtyper202511\Symfony\Component\Debug\ErrorHandler', 'Argtyper202511\Symfony\Component\HttpKernel\Debug\ExceptionHandler' => 'Argtyper202511\Symfony\Component\Debug\ExceptionHandler', 'Argtyper202511\Symfony\Component\HttpKernel\Exception\FatalErrorException' => 'Argtyper202511\Symfony\Component\Debug\Exception\FatalErrorException', 'Argtyper202511\Symfony\Component\HttpKernel\Exception\FlattenException' => 'Argtyper202511\Symfony\Component\Debug\Exception\FlattenException', 'Argtyper202511\Symfony\Component\HttpKernel\Log\LoggerInterface' => 'Argtyper202511\Psr\Log\LoggerInterface', 'Argtyper202511\Symfony\Component\HttpKernel\DependencyInjection\RegisterListenersPass' => 'Argtyper202511\Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass', 'Argtyper202511\Symfony\Component\HttpKernel\Log\NullLogger' => 'Argtyper202511\Psr\Log\LoggerInterface']);
};
