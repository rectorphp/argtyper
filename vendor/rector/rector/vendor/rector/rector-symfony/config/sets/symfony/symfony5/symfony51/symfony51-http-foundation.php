<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Rector\Config\RectorConfig;
use Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Rector\Transform\ValueObject\StaticCallToNew;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(StaticCallToNewRector::class, [new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\Response', 'create'), new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\JsonResponse', 'create'), new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\RedirectResponse', 'create'), new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\StreamedResponse', 'create')]);
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'none', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'lax', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'strict', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')]);
};
