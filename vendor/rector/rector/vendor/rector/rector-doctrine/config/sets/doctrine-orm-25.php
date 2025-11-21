<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Argtyper202511\Rector\Removing\ValueObject\ArgumentRemover;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Argtyper202511\\Doctrine\\ORM\\Mapping\\ClassMetadataFactory', 'setEntityManager', 0, new ObjectType('Argtyper202511\\Doctrine\\ORM\\EntityManagerInterface')), new AddParamTypeDeclaration('Argtyper202511\\Doctrine\\ORM\\Tools\\DebugUnitOfWorkListener', 'dumpIdentityMap', 0, new ObjectType('Argtyper202511\\Doctrine\\ORM\\EntityManagerInterface'))]);
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Argtyper202511\\Doctrine\\ORM\\Persisters\\Entity\\AbstractEntityInheritancePersister', 'getSelectJoinColumnSQL', 4, null)]);
};
