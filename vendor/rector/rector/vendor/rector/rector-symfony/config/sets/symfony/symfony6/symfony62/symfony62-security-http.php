<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Argtyper202511\Rector\Symfony\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(SecurityAttributeToIsGrantedAttributeRector::class);
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46907
        'Argtyper202511\\Sensio\\Bundle\\FrameworkExtraBundle\\Configuration\\IsGranted' => 'Argtyper202511\\Symfony\\Component\\Security\\Http\\Attribute\\IsGranted',
    ]);
    // @see https://github.com/symfony/symfony/pull/46094
    // @see https://github.com/symfony/symfony/pull/48554
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\Security', 'ACCESS_DENIED_ERROR', 'Argtyper202511\\Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'ACCESS_DENIED_ERROR'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\Security', 'AUTHENTICATION_ERROR', 'Argtyper202511\\Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'AUTHENTICATION_ERROR'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\Security', 'LAST_USERNAME', 'Argtyper202511\\Symfony\\Component\\Security\\Http\\SecurityRequestAttributes', 'LAST_USERNAME'), new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\Security', 'MAX_USERNAME_LENGTH', 'Argtyper202511\\Symfony\\Component\\Security\\Http\\Authenticator\\Passport\\Badge\\UserBadge', 'MAX_USERNAME_LENGTH')]);
};
