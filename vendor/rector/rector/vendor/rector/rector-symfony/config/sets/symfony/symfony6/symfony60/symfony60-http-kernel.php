<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\IterableType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $iterableType = new IterableType(new MixedType(), new MixedType());
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $httpFoundationResponseType = new ObjectType('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Response');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\KernelInterface', 'registerBundles', $iterableType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\CacheWarmer\\CacheWarmerInterface', 'isOptional', new BooleanType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\CacheWarmer\\WarmableInterface', 'warmUp', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\DataCollector\\DataCollector', 'getCasters', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\DataCollector\\DataCollectorInterface', 'getName', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\HttpCache\\HttpCache', 'forward', $httpFoundationResponseType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'doRequest', $httpFoundationResponseType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\HttpKernelBrowser', 'getScript', new StringType()), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'getLogs', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\HttpKernel\\Log\\DebugLoggerInterface', 'countErrors', new IntegerType())]);
};
