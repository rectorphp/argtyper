<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Argtyper202511\Symfony\Component\Yaml\Yaml', 'parse', 2, ['Argtyper202511\Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS'])]);
};
