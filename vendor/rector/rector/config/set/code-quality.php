<?php

declare (strict_types=1);
namespace Argtyper202511\RectorPrefix202511;

use Argtyper202511\Rector\Config\Level\CodeQualityLevel;
use Argtyper202511\Rector\Config\RectorConfig;
return static function (RectorConfig $rectorConfig) : void {
    foreach (CodeQualityLevel::RULES_WITH_CONFIGURATION as $rectorClass => $configuration) {
        $rectorConfig->ruleWithConfiguration($rectorClass, $configuration);
    }
    // the rule order matters, as its used in withCodeQualityLevel() method
    // place the safest rules first, follow by more complex ones
    $rectorConfig->rules(CodeQualityLevel::RULES);
};
