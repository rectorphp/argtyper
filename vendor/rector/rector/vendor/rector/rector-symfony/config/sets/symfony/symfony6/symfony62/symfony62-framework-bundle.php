<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Argtyper202511\Rector\Renaming\ValueObject\MethodCallRename;
use Argtyper202511\Rector\Symfony\Symfony62\Rector\MethodCall\SimplifyFormRenderingRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(SimplifyFormRenderingRector::class);
    $rectorConfig->ruleWithConfiguration(RenameMethodRector::class, [
        // @see https://github.com/symfony/symfony/pull/46854
        new MethodCallRename('Argtyper202511\\Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController', 'renderForm', 'render'),
    ]);
};
