<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Argtyper202511\Rector\Arguments\ValueObject\ArgumentAdder;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    # https://github.com/symfony/symfony/blob/4.4/UPGRADE-4.3.md#workflow
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Argtyper202511\\Symfony\\Component\\Workflow\\MarkingStore\\MarkingStoreInterface', 'setMarking', 2, 'context', [])]);
};
