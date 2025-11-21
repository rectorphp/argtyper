<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassConstFetch;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [
        new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\AuthenticationEvents', 'AUTHENTICATION_SUCCESS', 'Argtyper202511\\Symfony\\Component\\Security\\Core\\Event\\AuthenticationSuccessEvent', 'class'),
        new RenameClassAndConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\AuthenticationEvents', 'AUTHENTICATION_FAILURE', 'Argtyper202511\\Symfony\\Component\\Security\\Core\\Event\\AuthenticationFailureEvent', 'class'),
        // @see https://github.com/symfony/symfony/pull/42510
        new RenameClassConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter', 'IS_ANONYMOUS', 'PUBLIC_ACCESS'),
        new RenameClassConstFetch('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authorization\\Voter\\AuthenticatedVoter', 'IS_AUTHENTICATED_ANONYMOUSLY', 'PUBLIC_ACCESS'),
    ]);
};
