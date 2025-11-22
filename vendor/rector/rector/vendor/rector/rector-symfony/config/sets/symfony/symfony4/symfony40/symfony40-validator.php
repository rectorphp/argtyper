<?php

declare (strict_types=1);
namespace RectorPrefix202511;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony40\Rector\ConstFetch\ConstraintUrlOptionRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules([ConstraintUrlOptionRector::class]);
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\Validator\Tests\Constraints\AbstractConstraintValidatorTest' => 'Argtyper202511\Symfony\Component\Validator\Test\ConstraintValidatorTestCase']);
};
