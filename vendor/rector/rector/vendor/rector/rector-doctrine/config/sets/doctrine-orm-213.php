<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/doctrine/orm/pull/9876
        new MethodCallRename('Argtyper202511\\Doctrine\\ORM\\Event\\LifecycleEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Argtyper202511\\Doctrine\\ORM\\Event\\OnClearEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Argtyper202511\\Doctrine\\ORM\\Event\\OnFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Argtyper202511\\Doctrine\\ORM\\Event\\PostFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        new MethodCallRename('Argtyper202511\\Doctrine\\ORM\\Event\\PreFlushEventArgs', 'getEntityManager', 'getObjectManager'),
        // @see https://github.com/doctrine/orm/pull/9906
        new MethodCallRename('Argtyper202511\\Doctrine\\ORM\\Event\\LifecycleEventArgs', 'getEntity', 'getObject'),
        // @see https://github.com/doctrine/dbal/pull/4580
        new MethodCallRename('Argtyper202511\\Doctrine\\DBAL\\Statement', 'execute', 'executeQuery'),
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/doctrine/orm/pull/9906
        'Argtyper202511\\Doctrine\\ORM\\Event\\LifecycleEventArgs' => 'Argtyper202511\\Doctrine\\Persistence\\Event\\LifecycleEventArgs',
    ]);
};
