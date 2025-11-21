<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Contract\Rector;

use Argtyper202511\Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
interface ConfigurableRectorInterface extends \Argtyper202511\Rector\Contract\Rector\RectorInterface, ConfigurableRuleInterface
{
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void;
}
