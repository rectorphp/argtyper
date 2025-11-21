<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
use Argtyper202511\Rector\Symfony\Symfony40\Rector\ConstFetch\ConstraintUrlOptionRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->rules([ConstraintUrlOptionRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\\Symfony\\Component\\Validator\\Tests\\Constraints\\AbstractConstraintValidatorTest' => 'Argtyper202511\\Symfony\\Component\\Validator\\Test\\ConstraintValidatorTestCase']);
};
