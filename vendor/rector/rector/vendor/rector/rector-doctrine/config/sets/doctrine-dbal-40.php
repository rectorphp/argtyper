<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\Dbal40\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector;
use Argtyper202511\Rector\Doctrine\Dbal40\Rector\StmtsAwareInterface\ExecuteQueryParamsToBindValueRector;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-compositeexpression-methods
        ChangeCompositeExpressionAddMultipleWithWithRector::class,
        // @see https://github.com/doctrine/dbal/pull/5556
        ExecuteQueryParamsToBindValueRector::class,
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-misspelled-isfullfilledby-method
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Schema\\Index', 'isFullfilledBy', 'isFulfilledBy'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-expressionbuilder-methods
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'andX', 'and'),
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Query\\Expression\\ExpressionBuilder', 'orX', 'or'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-compositeexpression-methods
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Query\\Expression\\CompositeExpression', 'add', 'with'),
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removal-of-doctrine-cache
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Configuration', 'setResultCacheImpl', 'setResultCache'),
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Configuration', 'getResultCacheImpl', 'getResultCache'),
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\QueryCacheProfile', 'setResultCacheDriver', 'setResultCache'),
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\QueryCacheProfile', 'getResultCacheDriver', 'getResultCache'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-renamed-sqlite-platform-classes
        'Argtyper202511\\Doctrine\\DBAL\\Platforms\\SqlitePlatform' => 'Argtyper202511\\Doctrine\\DBAL\\Platforms\\SQLitePlatform',
        'Argtyper202511\\Doctrine\\DBAL\\Schema\\SqliteSchemaManager' => 'Argtyper202511\\Doctrine\\DBAL\\Schema\\SQLiteSchemaManager',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants
        new RenameClassAndConstFetch('Argtyper202511\\Doctrine\\DBAL\\Connection', 'PARAM_STR_ARRAY', 'Argtyper202511\\Doctrine\\DBAL\\ArrayParameterType', 'STRING'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connectionparam__array-constants
        new RenameClassAndConstFetch('Argtyper202511\\Doctrine\\DBAL\\Connection', 'PARAM_INT_ARRAY', 'Argtyper202511\\Doctrine\\DBAL\\ArrayParameterType', 'INTEGER'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/doctrine/dbal/blob/4.0.x/UPGRADE.md#bc-break-removed-connection_schemamanager-and-connectiongetschemamanager
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Connection', 'getSchemaManager', 'createSchemaManager'),
    ]);
};
