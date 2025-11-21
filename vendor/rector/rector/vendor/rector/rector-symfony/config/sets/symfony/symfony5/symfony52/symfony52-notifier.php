<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector;
use Rector\TypeDeclaration\ValueObject\AddParamTypeDeclaration;
return static function (RectorConfig $rectorConfig): void {
    # https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#notifier
    $rectorConfig->ruleWithConfiguration(AddParamTypeDeclarationRector::class, [new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Notifier\NotifierInterface', 'send', 1, new ObjectType('Argtyper202511\Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Notifier\Notifier', 'getChannels', 1, new ObjectType('Argtyper202511\Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Notifier\Channel\ChannelInterface', 'notify', 1, new ObjectType('Argtyper202511\Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Notifier\Channel\ChannelInterface', 'supports', 1, new ObjectType('Argtyper202511\Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Notifier\Notification\ChatNotificationInterface', 'asChatMessage', 0, new ObjectType('Argtyper202511\Symfony\Component\Notifier\Recipient\RecipientInterface')), new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Notifier\Notification\EmailNotificationInterface', 'asEmailMessage', 0, new ObjectType('Argtyper202511\Symfony\Component\Notifier\Recipient\EmailRecipientInterface')), new AddParamTypeDeclaration('Argtyper202511\Symfony\Component\Notifier\Notification\SmsNotificationInterface', 'asSmsMessage', 0, new ObjectType('Argtyper202511\Symfony\Component\Notifier\Recipient\SmsRecipientInterface'))]);
};
