<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\Removing\Rector\ClassMethod\ArgumentRemoverRector;
use Rector\Removing\ValueObject\ArgumentRemover;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Argtyper202601\Doctrine\ORM\Mapping\ClassMetadataFactory', 'setEntityManager', 0, new ObjectType('Argtyper202601\Doctrine\ORM\EntityManagerInterface')), new AddParamTypeDeclaration('Argtyper202601\Doctrine\ORM\Tools\DebugUnitOfWorkListener', 'dumpIdentityMap', 0, new ObjectType('Argtyper202601\Doctrine\ORM\EntityManagerInterface'))]);
    $rectorConfig->ruleWithConfiguration(ArgumentRemoverRector::class, [new ArgumentRemover('Argtyper202601\Doctrine\ORM\Persisters\Entity\AbstractEntityInheritancePersister', 'getSelectJoinColumnSQL', 4, null)]);
};
