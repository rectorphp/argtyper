<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Doctrine\Orm30\Rector\MethodCall\CastDoctrineExprToStringRector;
use Argtyper202511\Rector\Doctrine\Orm30\Rector\MethodCall\SetParametersArrayToCollectionRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([SetParametersArrayToCollectionRector::class, CastDoctrineExprToStringRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\\Doctrine\\ORM\\ORMException' => 'Argtyper202511\\Doctrine\\ORM\\Exception\\ORMException']);
};
