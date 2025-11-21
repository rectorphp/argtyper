<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony53\Rector\StaticPropertyFetch\KernelTestCaseContainerPropertyDeprecationRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([KernelTestCaseContainerPropertyDeprecationRector::class]);
};
