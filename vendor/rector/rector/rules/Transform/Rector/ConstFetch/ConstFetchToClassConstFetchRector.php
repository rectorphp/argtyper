<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\Rector\ConstFetch;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Transform\ValueObject\ConstFetchToClassConstFetch;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see Rector\Tests\Transform\Rector\ConstFetch\ConstFetchToClassConstFetchRector\ConstFetchToClassConstFetchTest
 */
final class ConstFetchToClassConstFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var ConstFetchToClassConstFetch[]
     */
    private $constFetchToClassConsts = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change const fetch to class const fetch', [new ConfiguredCodeSample('$x = CONTEXT_COURSE', '$x = course::LEVEL', [new ConstFetchToClassConstFetch('CONTEXT_COURSE', 'course', 'LEVEL')])]);
    }
    public function getNodeTypes(): array
    {
        return [ConstFetch::class];
    }
    public function refactor(Node $node): ?ClassConstFetch
    {
        foreach ($this->constFetchToClassConsts as $constFetchToClassConst) {
            if (!$this->isName($node, $constFetchToClassConst->getOldConstName())) {
                continue;
            }
            return $this->nodeFactory->createClassConstFetch($constFetchToClassConst->getNewClassName(), $constFetchToClassConst->getNewConstName());
        }
        return null;
    }
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, ConstFetchToClassConstFetch::class);
        $this->constFetchToClassConsts = $configuration;
    }
}
