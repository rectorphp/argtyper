<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\PropertyFetch\RenamePropertyRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Argtyper202511\Rector\Renaming\ValueObject\RenameProperty;
return static function (RectorConfig $rectorConfig): void {
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#security
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Argtyper202511\Symfony\Component\Security\Http\Firewall\AccessListener', 'PUBLIC_ACCESS', 'Argtyper202511\Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter', 'PUBLIC_ACCESS')]);
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#security
    $rectorConfig->ruleWithConfiguration(RenamePropertyRector::class, [new RenameProperty('Argtyper202511\Symfony\Component\Security\Http\RememberMe\AbstractRememberMeServices', 'providerKey', 'firewallName')]);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Argtyper202511\Symfony\Component\Security\Http\Authentication\DefaultAuthenticationSuccessHandler', 'getProviderKey', 'getFirewallName')]);
};
