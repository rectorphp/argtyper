<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\ObjectWithoutClassType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Symfony\Symfony60\Rector\FuncCall\ReplaceServiceArgumentRector;
use Argtyper202511\Rector\Symfony\ValueObject\ReplaceServiceArgument;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ReplaceServiceArgumentRector::class, [new ReplaceServiceArgument('Argtyper202511\Psr\Container\ContainerInterface', new String_('service_container')), new ReplaceServiceArgument('Argtyper202511\Symfony\Component\DependencyInjection\ContainerInterface', new String_('service_container'))]);
    $configurationType = new ObjectType('Argtyper202511\Symfony\Component\Config\Definition\ConfigurationInterface');
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $scalarTypes = [$arrayType, new BooleanType(), new StringType(), new IntegerType(), new FloatType(), new NullType()];
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass', 'processValue', new MixedType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface', 'getConfiguration', new UnionType([new NullType(), $configurationType])), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Extension\Extension', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Extension\Extension', 'getNamespace', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Extension\Extension', 'getConfiguration', new UnionType([new NullType(), $configurationType])), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Extension\ExtensionInterface', 'getNamespace', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Extension\ExtensionInterface', 'getXsdValidationBasePath', new UnionType([new StringType(), new ConstantBooleanType(\false)])), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Extension\ExtensionInterface', 'getAlias', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\LazyProxy\Instantiator\InstantiatorInterface', 'instantiateProxy', new ObjectWithoutClassType()), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\Container', 'getParameter', new UnionType($scalarTypes)), new AddReturnTypeDeclaration('Argtyper202511\Symfony\Component\DependencyInjection\ContainerInterface', 'getParameter', new UnionType($scalarTypes))]);
};
