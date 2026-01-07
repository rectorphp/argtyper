<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // EventDispatcher
        'Argtyper202601\Symfony\Component\HttpKernel\Event\FilterControllerArgumentsEvent' => 'Argtyper202601\Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent',
        'Argtyper202601\Symfony\Component\HttpKernel\Event\FilterControllerEvent' => 'Argtyper202601\Symfony\Component\HttpKernel\Event\ControllerEvent',
        'Argtyper202601\Symfony\Component\HttpKernel\Event\FilterResponseEvent' => 'Argtyper202601\Symfony\Component\HttpKernel\Event\ResponseEvent',
        'Argtyper202601\Symfony\Component\HttpKernel\Event\GetResponseEvent' => 'Argtyper202601\Symfony\Component\HttpKernel\Event\RequestEvent',
        'Argtyper202601\Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent' => 'Argtyper202601\Symfony\Component\HttpKernel\Event\ViewEvent',
        'Argtyper202601\Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent' => 'Argtyper202601\Symfony\Component\HttpKernel\Event\ExceptionEvent',
        'Argtyper202601\Symfony\Component\HttpKernel\Event\PostResponseEvent' => 'Argtyper202601\Symfony\Component\HttpKernel\Event\TerminateEvent',
        // @todo unpack after YAML to PHP migration, Symfony\Component\HttpKernel\Client: Symfony\Component\HttpKernel\HttpKernelBrowser
        'Argtyper202601\Symfony\Component\HttpKernel\EventListener\TranslatorListener' => 'Argtyper202601\Symfony\Component\HttpKernel\EventListener\LocaleAwareListener',
    ]);
};
