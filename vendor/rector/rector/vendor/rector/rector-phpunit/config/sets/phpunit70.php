<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\PHPUnit\PHPUnit70\Rector\Class_\RemoveDataProviderTestPrefixRector;
use Argtyper202511\Rector\Renaming\Rector\ClassMethod\RenameAnnotationRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameAnnotationByType;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameAnnotationRector::class, [new RenameAnnotationByType('Argtyper202511\\PHPUnit\\Framework\\TestCase', 'scenario', 'test')]);
    $rectorConfig->rule(RemoveDataProviderTestPrefixRector::class);
};
