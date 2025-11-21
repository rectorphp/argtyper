<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\Level\TypeDeclarationDocblocksLevel;
use Argtyper202511\Rector\Config\RectorConfig;
/**
 * @experimental * 2025-09, experimental hidden set for type declaration in docblocks
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rules(TypeDeclarationDocblocksLevel::RULES);
};
