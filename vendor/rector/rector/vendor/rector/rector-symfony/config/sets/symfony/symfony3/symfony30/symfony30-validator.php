<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202601\Symfony\Component\Validator\Constraints\Collection\Optional' => 'Argtyper202601\Symfony\Component\Validator\Constraints\Optional', 'Argtyper202601\Symfony\Component\Validator\Constraints\Collection\Required' => 'Argtyper202601\Symfony\Component\Validator\Constraints\Required', 'Argtyper202601\Symfony\Component\Validator\MetadataInterface' => 'Argtyper202601\Symfony\Component\Validator\Mapping\MetadataInterface', 'Argtyper202601\Symfony\Component\Validator\PropertyMetadataInterface' => 'Argtyper202601\Symfony\Component\Validator\Mapping\PropertyMetadataInterface', 'Argtyper202601\Symfony\Component\Validator\PropertyMetadataContainerInterface' => 'Argtyper202601\Symfony\Component\Validator\Mapping\ClassMetadataInterface', 'Argtyper202601\Symfony\Component\Validator\ClassBasedInterface' => 'Argtyper202601\Symfony\Component\Validator\Mapping\ClassMetadataInterface', 'Argtyper202601\Symfony\Component\Validator\Mapping\ElementMetadata' => 'Argtyper202601\Symfony\Component\Validator\Mapping\GenericMetadata', 'Argtyper202601\Symfony\Component\Validator\ExecutionContextInterface' => 'Argtyper202601\Symfony\Component\Validator\Context\ExecutionContextInterface', 'Argtyper202601\Symfony\Component\Validator\Mapping\ClassMetadataFactory' => 'Argtyper202601\Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory', 'Argtyper202601\Symfony\Component\Validator\Mapping\MetadataFactoryInterface' => 'Argtyper202601\Symfony\Component\Validator\Mapping\Factory\MetadataFactoryInterface']);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202601\Symfony\Component\Validator\ConstraintViolationInterface', 'getMessageParameters', 'getParameters'), new MethodCallRename('Argtyper202601\Symfony\Component\Validator\ConstraintViolationInterface', 'getMessagePluralization', 'getPlural'), new MethodCallRename('Argtyper202601\Symfony\Component\Validator\ConstraintViolation', 'getMessageParameters', 'getParameters'), new MethodCallRename('Argtyper202601\Symfony\Component\Validator\ConstraintViolation', 'getMessagePluralization', 'getPlural')]);
};
