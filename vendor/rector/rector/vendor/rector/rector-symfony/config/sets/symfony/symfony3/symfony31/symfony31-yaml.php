<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Argtyper202511\Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Yaml\Yaml', 'parse', 1, [\false, \false, \true], 'Argtyper202511\Symfony\Component\Yaml\Yaml::PARSE_OBJECT_FOR_MAP'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Yaml\Yaml', 'parse', 1, [\false, \true], 'Argtyper202511\Symfony\Component\Yaml\Yaml::PARSE_OBJECT'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Yaml\Yaml', 'parse', 1, \true, 'Argtyper202511\Symfony\Component\Yaml\Yaml::PARSE_EXCEPTION_ON_INVALID_TYPE'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Yaml\Yaml', 'parse', 1, \false, 0), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Yaml\Yaml', 'dump', 3, [\false, \true], 'Argtyper202511\Symfony\Component\Yaml\Yaml::DUMP_OBJECT'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\Yaml\Yaml', 'dump', 3, \true, 'Argtyper202511\Symfony\Component\Yaml\Yaml::DUMP_EXCEPTION_ON_INVALID_TYPE')]);
};
