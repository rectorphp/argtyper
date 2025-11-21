<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Symfony\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rule(ErrorNamesPropertyToConstantRector::class);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/pull/45623
        'Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntax' => 'Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\ExpressionSyntax',
        'Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\ExpressionLanguageSyntaxValidator' => 'Argtyper202511\\Symfony\\Component\\Validator\\Constraints\\ExpressionSyntaxValidator',
    ]);
};
