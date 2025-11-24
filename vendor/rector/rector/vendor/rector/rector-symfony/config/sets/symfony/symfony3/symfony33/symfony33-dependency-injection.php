<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ArgumentAdderRector::class, [new ArgumentAdder('Argtyper202511\Symfony\Component\DependencyInjection\ContainerBuilder', 'compile', 0, 'resolveEnvPlaceholders', \false), new ArgumentAdder('Argtyper202511\Symfony\Component\DependencyInjection\ContainerBuilder', 'addCompilerPass', 2, 'priority', 0), new ArgumentAdder('Argtyper202511\Symfony\Component\DependencyInjection\Compiler\ServiceReferenceGraph', 'connect', 6, 'weak', \false)]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\DependencyInjection\DefinitionDecorator' => 'Argtyper202511\Symfony\Component\DependencyInjection\ChildDefinition']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\DependencyInjection\Container', 'isFrozen', 'isCompiled')]);
};
