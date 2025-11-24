<?php

declare (strict_types=1);
namespace Argtyper202511\Symplify\RuleDocGenerator\Contract;

use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @api
 */
interface DocumentedRuleInterface
{
    public function getRuleDefinition(): RuleDefinition;
}
