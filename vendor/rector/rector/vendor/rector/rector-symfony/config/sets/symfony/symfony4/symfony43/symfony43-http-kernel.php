<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // EventDispatcher
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\FilterControllerArgumentsEvent' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\ControllerArgumentsEvent',
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\ControllerEvent',
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\ResponseEvent',
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\RequestEvent',
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\GetResponseForControllerResultEvent' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\ViewEvent',
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\GetResponseForExceptionEvent' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\ExceptionEvent',
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\Event\\TerminateEvent',
        // @todo unpack after YAML to PHP migration, Symfony\Component\HttpKernel\Client: Symfony\Component\HttpKernel\HttpKernelBrowser
        'Argtyper202511\\Symfony\\Component\\HttpKernel\\EventListener\\TranslatorListener' => 'Argtyper202511\\Symfony\\Component\\HttpKernel\\EventListener\\LocaleAwareListener',
    ]);
};
