<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\PHPUnit\PHPUnit120\Rector\Class_\AssertIsTypeMethodCallRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit120\Rector\Class_\RemoveOverrideFinalConstructTestCaseRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([RemoveOverrideFinalConstructTestCaseRector::class, AssertIsTypeMethodCallRector::class]);
};
