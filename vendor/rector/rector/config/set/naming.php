<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Naming\Rector\Assign\RenameVariableToMatchMethodCallReturnTypeRector;
use Argtyper202511\Rector\Naming\Rector\Class_\RenamePropertyToMatchTypeRector;
use Argtyper202511\Rector\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector;
use Argtyper202511\Rector\Naming\Rector\ClassMethod\RenameVariableToMatchNewTypeRector;
use Argtyper202511\Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchExprVariableRector;
use Argtyper202511\Rector\Naming\Rector\Foreach_\RenameForeachValueVariableToMatchMethodCallReturnTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([RenameParamToMatchTypeRector::class, RenamePropertyToMatchTypeRector::class, RenameVariableToMatchNewTypeRector::class, RenameVariableToMatchMethodCallReturnTypeRector::class, RenameForeachValueVariableToMatchMethodCallReturnTypeRector::class, RenameForeachValueVariableToMatchExprVariableRector::class]);
};
