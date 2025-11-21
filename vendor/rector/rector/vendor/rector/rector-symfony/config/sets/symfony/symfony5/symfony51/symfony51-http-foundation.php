<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Arguments\Rector\ClassMethod\ReplaceArgumentDefaultValueRector;
use Argtyper202511\Rector\Arguments\ValueObject\ReplaceArgumentDefaultValue;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Transform\Rector\StaticCall\StaticCallToNewRector;
use Argtyper202511\Rector\Transform\ValueObject\StaticCallToNew;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(StaticCallToNewRector::class, [new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\Response', 'create'), new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\JsonResponse', 'create'), new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\RedirectResponse', 'create'), new StaticCallToNew('Argtyper202511\Symfony\Component\HttpFoundation\StreamedResponse', 'create')]);
    $rectorConfig->ruleWithConfiguration(ReplaceArgumentDefaultValueRector::class, [new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'none', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_NONE'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'lax', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_LAX'), new ReplaceArgumentDefaultValue('Argtyper202511\Symfony\Component\HttpFoundation\Cookie', 'withSameSite', 0, 'strict', 'Argtyper202511\Symfony\Component\HttpFoundation\Cookie::SAMESITE_STRICT')]);
};
