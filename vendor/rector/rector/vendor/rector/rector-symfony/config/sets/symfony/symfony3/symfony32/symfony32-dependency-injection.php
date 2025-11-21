<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Argtyper202511\Rector\Arguments\ValueObject\ArgumentAdder;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Argtyper202511\Symfony\Component\DependencyInjection\ContainerBuilder', 'addCompilerPass', 2, 'priority', 0)]);
};
