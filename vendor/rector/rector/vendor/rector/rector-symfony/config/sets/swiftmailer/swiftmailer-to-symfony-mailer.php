<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\ClassConstFetch\RenameClassConstFetchRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\RenameClassAndConstFetch;
use Argtyper202511\Rector\Symfony\SwiftMailer\Rector\ClassMethod\SwiftMessageToEmailRector;
use Argtyper202511\Rector\Symfony\SwiftMailer\Rector\MethodCall\SwiftCreateMessageToNewEmailRector;
use Argtyper202511\Rector\Symfony\SwiftMailer\Rector\MethodCall\SwiftSetBodyToHtmlPlainMethodCallRector;
return static function (RectorConfig $rectorConfig): void {
    // @see https://symfony.com/blog/the-end-of-swiftmailer
    $rectorConfig->rules([SwiftCreateMessageToNewEmailRector::class, SwiftSetBodyToHtmlPlainMethodCallRector::class, SwiftMessageToEmailRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        'Swift_Mailer' => 'Argtyper202511\Symfony\Component\Mailer\MailerInterface',
        // message
        'Swift_Mime_SimpleMessage' => 'Argtyper202511\Symfony\Component\Mime\RawMessage',
        // transport
        'Swift_SmtpTransport' => 'Argtyper202511\Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport',
        'Swift_FailoverTransport' => 'Argtyper202511\Symfony\Component\Mailer\Transport\FailoverTransport',
        'Swift_SendmailTransport' => 'Argtyper202511\Symfony\Component\Mailer\Transport\SendmailTransport',
    ]);
    $rectorConfig->ruleWithConfiguration(RenameClassConstFetchRector::class, [new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_HIGHEST', 'Argtyper202511\Symfony\Component\Mime\Email', 'PRIORITY_HIGHEST'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_HIGH', 'Argtyper202511\Symfony\Component\Mime\Email', 'PRIORITY_HIGH'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_NORMAL', 'Argtyper202511\Symfony\Component\Mime\Email', 'PRIORITY_NORMAL'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_LOW', 'Argtyper202511\Symfony\Component\Mime\Email', 'PRIORITY_LOW'), new RenameClassAndConstFetch('Swift_Mime_SimpleMessage', 'PRIORITY_LOWEST', 'Argtyper202511\Symfony\Component\Mime\Email', 'PRIORITY_LOWEST')]);
};
