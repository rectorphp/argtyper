<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony25\Rector\MethodCall\AddViolationToBuildViolationRector;
use Argtyper202511\Rector\Symfony\Symfony25\Rector\MethodCall\MaxLengthSymfonyFormOptionToAttrRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([AddViolationToBuildViolationRector::class, MaxLengthSymfonyFormOptionToAttrRector::class]);
};
