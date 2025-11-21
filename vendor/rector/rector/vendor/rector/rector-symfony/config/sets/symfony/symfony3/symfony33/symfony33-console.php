<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Symfony\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ConsoleExceptionToErrorEventConstantRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // console
        'Argtyper202511\\Symfony\\Component\\Console\\Event\\ConsoleExceptionEvent' => 'Argtyper202511\\Symfony\\Component\\Console\\Event\\ConsoleErrorEvent',
    ]);
};
