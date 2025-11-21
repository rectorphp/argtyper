<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
// https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.0.md
// @see https://github.com/symfony/symfony/blob/6.1/.github/expected-missing-return-types.diff
return static function (RectorConfig $rectorConfig) : void {
    $arrayType = new ArrayType(new MixedType(), new MixedType());
    $httpFoundationResponseType = new ObjectType('Argtyper202511\\Symfony\\Component\\HttpFoundation\\Response');
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Http\\EntryPoint\\AuthenticationEntryPointInterface', 'start', $httpFoundationResponseType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Http\\Firewall', 'getSubscribedEvents', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Http\\FirewallMapInterface', 'getListeners', $arrayType), new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Http\\Authenticator\\AuthenticatorInterface', 'authenticate', new ObjectType('Argtyper202511\\Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Passport'))]);
};
