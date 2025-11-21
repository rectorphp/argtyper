<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameAttribute;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameAttributeRector::class, [new RenameAttribute('Argtyper202511\\Symfony\\Component\\Routing\\Annotation\\Route', 'Argtyper202511\\Symfony\\Component\\Routing\\Attribute\\Route')]);
};
