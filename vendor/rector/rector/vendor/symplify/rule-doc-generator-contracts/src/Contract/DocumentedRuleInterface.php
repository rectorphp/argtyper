<?php

declare (strict_types=1);
namespace Argtyper202601\Symplify\RuleDocGenerator\Contract;

use Argtyper202601\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @api
 */
interface DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition;
}
