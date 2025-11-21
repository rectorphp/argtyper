<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\RectorConfig;
use Argtyper202511\Rector\Renaming\Rector\Name\RenameClassRector;
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        # Security
        'Argtyper202511\\Symfony\\Component\\Security\\Core\\Encoder\\Argon2iPasswordEncoder' => 'Argtyper202511\\Symfony\\Component\\Security\\Core\\Encoder\\SodiumPasswordEncoder',
        'Argtyper202511\\Symfony\\Component\\Security\\Core\\Encoder\\BCryptPasswordEncoder' => 'Argtyper202511\\Symfony\\Component\\Security\\Core\\Encoder\\NativePasswordEncoder',
    ]);
};
