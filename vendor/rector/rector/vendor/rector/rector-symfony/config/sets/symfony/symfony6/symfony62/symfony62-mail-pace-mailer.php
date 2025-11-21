<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    // https://symfony.com/blog/new-in-symfony-6-2-built-in-cache-security-template-and-doctrine-attributes
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/46714
        'Argtyper202511\\Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpApiTransport' => 'Argtyper202511\\Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceApiTransport',
        'Argtyper202511\\Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpSmtpTransport' => 'Argtyper202511\\Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceSmtpTransport',
        'Argtyper202511\\Symfony\\Component\\Mailer\\Bridge\\OhMySmtp\\Transport\\OhMySmtpTransportFactory' => 'Argtyper202511\\Symfony\\Component\\Mailer\\Bridge\\MailPace\\Transport\\MailPaceTransportFactory',
    ]);
};
