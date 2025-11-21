<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\VoidType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\PHPUnit\PHPUnit80\Rector\MethodCall\AssertEqualsParameterToSpecificMethodsTypeRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit80\Rector\MethodCall\SpecificAssertContainsRector;
use Argtyper202511\Rector\PHPUnit\PHPUnit80\Rector\MethodCall\SpecificAssertInternalTypeRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Argtyper202511\Rector\ValueObject\MethodName;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([SpecificAssertInternalTypeRector::class, AssertEqualsParameterToSpecificMethodsTypeRector::class, SpecificAssertContainsRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # https://github.com/sebastianbergmann/phpunit/issues/3123
        'PHPUnit_Framework_MockObject_MockObject' => 'Argtyper202511\PHPUnit\Framework\MockObject\MockObject',
    ]);
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [
        // https://github.com/rectorphp/rector/issues/1024 - no type, $dataName
        new AddParamTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', MethodName::CONSTRUCT, 2, new MixedType()),
    ]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', 'setUpBeforeClass', new VoidType()), new AddReturnTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', 'setUp', new VoidType()), new AddReturnTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', 'assertPreConditions', new VoidType()), new AddReturnTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', 'assertPostConditions', new VoidType()), new AddReturnTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', 'tearDown', new VoidType()), new AddReturnTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', 'tearDownAfterClass', new VoidType()), new AddReturnTypeDeclaration('Argtyper202511\PHPUnit\Framework\TestCase', 'onNotSuccessfulTest', new VoidType())]);
};
