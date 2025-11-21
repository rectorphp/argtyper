<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
// @see https://github.com/symfony/symfony/blob/6.4/UPGRADE-6.4.md
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, ['Argtyper202511\Symfony\Component\HttpKernel\Debug\FileLinkFormatter' => 'Argtyper202511\Symfony\Component\ErrorHandler\ErrorRenderer\FileLinkFormatter']);
};
