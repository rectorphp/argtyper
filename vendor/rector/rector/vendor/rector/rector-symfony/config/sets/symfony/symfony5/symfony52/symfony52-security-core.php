<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\PreAuthenticatedToken', 'getProviderKey', 'getFirewallName'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken', 'getProviderKey', 'getFirewallName'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\SwitchUserToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\SwitchUserToken', 'getProviderKey', 'getFirewallName'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\UsernamePasswordToken', 'setProviderKey', 'setFirewallName'), new MethodCallRename('Argtyper202511\\Symfony\\Component\\Security\\Core\\Authentication\\Token\\UsernamePasswordToken', 'getProviderKey', 'getFirewallName')]);
};
