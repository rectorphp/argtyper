<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\Level\TypeDeclarationLevel;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig): void {
    // the rule order matters, as its used in withTypeCoverageLevel() method
    // place the safest rules first, follow by more complex ones
    $rectorConfig->rules(TypeDeclarationLevel::RULES);
};
