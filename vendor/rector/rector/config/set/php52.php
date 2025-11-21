<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php52\Rector\Property\VarToPublicPropertyRector;
use Argtyper202511\Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector;
use Argtyper202511\Rector\Removing\Rector\FuncCall\RemoveFuncCallArgRector;
use Argtyper202511\Rector\Removing\ValueObject\RemoveFuncCallArg;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([VarToPublicPropertyRector::class, ContinueToBreakInSwitchRector::class]);
    $rectorConfig->ruleWithConfiguration(RemoveFuncCallArgRector::class, [
        // see https://www.php.net/manual/en/function.ldap-first-attribute.php
        new RemoveFuncCallArg('ldap_first_attribute', 2),
    ]);
};
