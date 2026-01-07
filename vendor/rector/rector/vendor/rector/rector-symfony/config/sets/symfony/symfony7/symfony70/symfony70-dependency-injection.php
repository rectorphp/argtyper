<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Class_\RenameAttributeRector;
use Rector\Renaming\ValueObject\RenameAttribute;
return static function (RectorConfig $rectorConfig): void {
    // @see https://github.com/symfony/symfony/blob/7.0/UPGRADE-7.0.md#dependencyinjection
    $rectorConfig->ruleWithConfiguration(RenameAttributeRector::class, [new RenameAttribute('Argtyper202601\Symfony\Component\DependencyInjection\Attribute\MapDecorated', 'Argtyper202601\Symfony\Component\DependencyInjection\Attribute\AutowireDecorated')]);
};
