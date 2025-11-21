<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php55\Rector\Class_\ClassConstantToSelfClassRector;
use Argtyper202511\Rector\Php55\Rector\ClassConstFetch\StaticToSelfOnFinalClassRector;
use Argtyper202511\Rector\Php55\Rector\FuncCall\GetCalledClassToSelfClassRector;
use Argtyper202511\Rector\Php55\Rector\FuncCall\GetCalledClassToStaticClassRector;
use Argtyper202511\Rector\Php55\Rector\FuncCall\PregReplaceEModifierRector;
use Argtyper202511\Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([StringClassNameToClassConstantRector::class, ClassConstantToSelfClassRector::class, PregReplaceEModifierRector::class, GetCalledClassToSelfClassRector::class, GetCalledClassToStaticClassRector::class, StaticToSelfOnFinalClassRector::class]);
};
