<?php

declare (strict_types=1);
namespace RectorPrefix202512;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # Security
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\Argon2iPasswordEncoder' => 'Argtyper202601\Symfony\Component\Security\Core\Encoder\SodiumPasswordEncoder',
        'Argtyper202601\Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder' => 'Argtyper202601\Symfony\Component\Security\Core\Encoder\NativePasswordEncoder',
    ]);
};
