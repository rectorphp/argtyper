<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'lax', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', '__construct', 8, 'strict', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')]);
};
