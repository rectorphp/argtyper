<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Symfony\Symfony27\Rector\MethodCall\ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ChangeCollectionTypeOptionNameFromTypeToEntryTypeRector::class);
    // @see https://symfony.com/blog/new-in-symfony-2-7-form-and-validator-updates#deprecated-setdefaultoptions-in-favor-of-configureoptions
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [new MethodCallRename('Argtyper202511\\Symfony\\Component\\Form\\AbstractType', 'setDefaultOptions', 'configureOptions')]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\\Symfony\\Component\\OptionsResolver\\OptionsResolverInterface' => 'Argtyper202511\\Symfony\\Component\\OptionsResolver\\OptionsResolver']);
};
