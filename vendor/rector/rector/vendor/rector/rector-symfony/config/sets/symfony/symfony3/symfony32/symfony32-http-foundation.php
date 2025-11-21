<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Argtyper202511\Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'lax', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'strict', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')]);
};
