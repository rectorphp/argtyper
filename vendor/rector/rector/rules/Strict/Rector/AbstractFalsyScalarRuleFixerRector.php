<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Strict\Rector;

use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @internal
 */
abstract class AbstractFalsyScalarRuleFixerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const TREAT_AS_NON_EMPTY = 'treat_as_non_empty';
    /**
     * @var bool
     */
    protected $treatAsNonEmpty = \false;
    /**
     * @param array<string, mixed> $configuration
     */
    public function configure(array $configuration) : void
    {
        $treatAsNonEmpty = $configuration[self::TREAT_AS_NON_EMPTY] ?? (bool) \current($configuration);
        Assert::boolean($treatAsNonEmpty);
        $this->treatAsNonEmpty = $treatAsNonEmpty;
    }
}
