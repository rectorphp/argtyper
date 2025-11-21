<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Argtyper202511\Rector\TypeDeclaration\ValueObject\AddReturnTypeDeclaration;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'loadUserByUsername', 'loadUserByIdentifier'),
        // @see https://github.com/rectorphp/rector-symfony/issues/112
        new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getUsername', 'getUserIdentifier'),
    ]);
    $rectorConfig->ruleWithConfiguration(AddReturnTypeDeclarationRector::class, [
        // @see https://wouterj.nl/2021/09/symfony-6-native-typing#when-upgrading-to-symfony-54
        new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Core\\User\\UserInterface', 'getRoles', new ArrayType(new MixedType(), new MixedType())),
        new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\TokenProviderInterface', 'loadTokenBySeries', new ObjectType('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\RememberMe\\PersistentTokenInterface')),
        new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface', 'vote', new IntegerType()),
        new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Core\\Exception\\AuthenticationException', 'getMessageKey', new StringType()),
        new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'refreshUser', new ObjectType('Argtyper202511\\Symfony\\Component\\Security\\Core\\User\\UserInterface')),
        new AddReturnTypeDeclaration('Argtyper202511\\Symfony\\Component\\Security\\Core\\User\\UserProviderInterface', 'supportsClass', new BooleanType()),
    ]);
};
