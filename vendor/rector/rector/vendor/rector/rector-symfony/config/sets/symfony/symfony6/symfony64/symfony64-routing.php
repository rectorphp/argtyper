<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Rector\Renaming\ValueObject\RenameAttribute;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameAttributeRector::class, [new RenameAttribute('Argtyper202511\Symfony\Component\Routing\Annotation\Route', 'Argtyper202511\Symfony\Component\Routing\Attribute\Route')]);
};
