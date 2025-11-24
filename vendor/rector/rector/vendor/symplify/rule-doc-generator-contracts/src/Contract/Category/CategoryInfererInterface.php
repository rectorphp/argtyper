<?php

declare (strict_types=1);
namespace Argtyper202511\Symplify\RuleDocGenerator\Contract\Category;

use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface CategoryInfererInterface
{
    public function infer(RuleDefinition $ruleDefinition): ?string;
}
