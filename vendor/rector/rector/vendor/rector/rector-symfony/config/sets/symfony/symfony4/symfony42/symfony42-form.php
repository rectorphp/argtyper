<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\IterableType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Transform\Rector\ClassMethod\WrapReturnRector;
use Argtyper202511\Rector\Transform\ValueObject\WrapReturn;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
use Argtyper202511\Rector\ValueObject\Visibility;
use Argtyper202511\Rector\Visibility\Rector\ClassMethod\ChangeMethodVisibilityRector;
use Argtyper202511\Rector\Visibility\ValueObject\ChangeMethodVisibility;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\\Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedType', 'getExtendedTypes')]);
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedTypes', $iterableType)]);
    $rectorConfig->ruleWithConfiguration(ChangeMethodVisibilityRector::class, [new ChangeMethodVisibility('Argtyper202511\\Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedTypes', Visibility::STATIC)]);
    $rectorConfig->ruleWithConfiguration(WrapReturnRector::class, [new WrapReturn('Argtyper202511\\Symfony\\Component\\Form\\AbstractTypeExtension', 'getExtendedTypes', \true)]);
};
