<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\Workflow\DefinitionBuilder', 'reset', 'clear'), new MethodCallRename('Argtyper202511\Symfony\Component\Workflow\DefinitionBuilder', 'add', 'addWorkflow')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\Workflow\SupportStrategy\SupportStrategyInterface' => 'Argtyper202511\Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface', 'Argtyper202511\Symfony\Component\Workflow\SupportStrategy\ClassInstanceSupportStrategy' => 'Argtyper202511\Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy']);
};
