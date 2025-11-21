<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Argtyper202511\Rector\Removing\ValueObject\ArgumentRemover;
use Argtyper202511\Rector\ValueObject\MethodName;
return static function (RectorConfig $rectorConfig): void {
    # https://github.com/symfony/symfony/commit/f5c355e1ba399a1b3512367647d902148bdaf09f
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Argtyper202511\Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector', MethodName::CONSTRUCT, 0, null), new ArgumentRemover('Argtyper202511\Symfony\Component\HttpKernel\DataCollector\ConfigDataCollector', MethodName::CONSTRUCT, 1, null)]);
};
