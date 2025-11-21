<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Argtyper202511\Rector\Removing\ValueObject\ArgumentRemover;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Argtyper202511\Symfony\Component\Yaml\Yaml', 'parse', 2, ['Argtyper202511\Symfony\Component\Yaml\Yaml::PARSE_KEYS_AS_STRINGS'])]);
};
