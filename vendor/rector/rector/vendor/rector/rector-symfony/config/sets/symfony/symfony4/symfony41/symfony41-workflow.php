<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\Workflow\DefinitionBuilder', 'reset', 'clear'), new MethodCallRename('Argtyper202511\Symfony\Component\Workflow\DefinitionBuilder', 'add', 'addWorkflow')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\Workflow\SupportStrategy\SupportStrategyInterface' => 'Argtyper202511\Symfony\Component\Workflow\SupportStrategy\WorkflowSupportStrategyInterface', 'Argtyper202511\Symfony\Component\Workflow\SupportStrategy\ClassInstanceSupportStrategy' => 'Argtyper202511\Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy']);
};
