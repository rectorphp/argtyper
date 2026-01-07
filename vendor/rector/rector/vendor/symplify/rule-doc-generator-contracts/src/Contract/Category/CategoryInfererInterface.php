<?php

declare (strict_types=1);
namespace Argtyper202601\Symplify\RuleDocGenerator\Contract\Category;

use Argtyper202601\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
interface CategoryInfererInterface
{
    public function infer(RuleDefinition $ruleDefinition): ?string;
}
