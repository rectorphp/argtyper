<?php

declare(strict_types=1);

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

return (new Configuration())
    // used via both Rector and PHPStan deps
    ->ignoreErrorsOnPackage('nikic/php-parser', [ErrorType::SHADOW_DEPENDENCY]);
