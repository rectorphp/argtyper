<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202601\Symfony\Component\Security\Core\Authentication\Token\TokenInterface', 'getUsername', 'getUserIdentifier'), new MethodCallRename('Argtyper202601\Symfony\Component\Security\Core\Exception\UsernameNotFoundException', 'getUsername', 'getUserIdentifier'), new MethodCallRename('Argtyper202601\Symfony\Component\Security\Core\Exception\UsernameNotFoundException', 'setUsername', 'setUserIdentifier'), new MethodCallRename('Argtyper202601\Symfony\Component\Security\Core\Authentication\RememberMe\PersistentTokenInterface', 'getUsername', 'getUserIdentifier')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Argtyper202601\Symfony\Component\Security\Core\Exception\UsernameNotFoundException' => 'Argtyper202601\Symfony\Component\Security\Core\Exception\UserNotFoundException',
        // @see https://github.com/symfony/symfony/pull/39802
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\MessageDigestPasswordHasher',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\MigratingPasswordEncoder' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\MigratingPasswordHasher',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\NativePasswordEncoder' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\NativePasswordHasher',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface' => 'Argtyper202601\Symfony\Component\PasswordHasher\PasswordHasherInterface',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\Pbkdf2PasswordEncoder' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\Pbkdf2PasswordHasher',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\PlaintextPasswordEncoder' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\PlaintextPasswordHasher',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\SelfSaltingEncoderInterface' => 'Argtyper202601\Symfony\Component\PasswordHasher\LegacyPasswordHasherInterface',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\SodiumPasswordEncoder' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\SodiumPasswordHasher',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\UserPasswordEncoder' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface' => 'Argtyper202601\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface',
    ]);
};
