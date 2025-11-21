<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\StaticCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Rector\Transform\ValueObject\StaticCallToFuncCall;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\StaticCall\StaticCallToFuncCallRector\StaticCallToFuncCallRectorTest
 */
final class StaticCallToFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var StaticCallToFuncCall[]
     */
    private $staticCallsToFunctions = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turn static call to function call', [new ConfiguredCodeSample('OldClass::oldMethod("args");', 'new_function("args");', [new StaticCallToFuncCall('OldClass', 'oldMethod', 'new_function')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->staticCallsToFunctions as $staticCallToFunction) {
            if (!$this->isName($node->name, $staticCallToFunction->getMethod())) {
                continue;
            }
            if (!$this->isObjectType($node->class, $staticCallToFunction->getObjectType())) {
                continue;
            }
            return new FuncCall(new FullyQualified($staticCallToFunction->getFunction()), $node->args);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, StaticCallToFuncCall::class);
        $this->staticCallsToFunctions = $configuration;
    }
}
