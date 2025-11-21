<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Php83\Rector\BooleanAnd\JsonValidateRector;
use Argtyper202511\Rector\Php83\Rector\Class_\ReadOnlyAnonymousClassRector;
use Argtyper202511\Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Argtyper202511\Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;
use Argtyper202511\Rector\Php83\Rector\FuncCall\CombineHostPortLdapUriRector;
use Argtyper202511\Rector\Php83\Rector\FuncCall\DynamicClassConstFetchRector;
use Argtyper202511\Rector\Php83\Rector\FuncCall\RemoveGetClassGetParentClassNoArgsRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([AddOverrideAttributeToOverriddenMethodsRector::class, AddTypeToConstRector::class, CombineHostPortLdapUriRector::class, RemoveGetClassGetParentClassNoArgsRector::class, ReadOnlyAnonymousClassRector::class, DynamicClassConstFetchRector::class, JsonValidateRector::class]);
};
