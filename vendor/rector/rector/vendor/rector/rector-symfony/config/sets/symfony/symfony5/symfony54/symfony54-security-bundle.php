<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/42582
        new MethodCallRename('Argtyper202511\Symfony\Bundle\SecurityBundle\Security\FirewallConfig', 'getListeners', 'getAuthenticators'),
        // @see https://github.com/symfony/symfony/pull/41754
        new MethodCallRename('Argtyper202511\Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension', 'addSecurityListenerFactory', 'addAuthenticatorFactory'),
    ]);
};
