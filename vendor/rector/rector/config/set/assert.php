<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Assert\Rector\ClassMethod\AddAssertArrayFromClassMethodDocblockRector;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddAssertArrayFromClassMethodDocblockRector::class]);
};
