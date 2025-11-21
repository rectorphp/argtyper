<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Removing\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Removing\ValueObject\RemoveFuncCallArg;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\FuncCall\RemoveFuncCallArgRector\RemoveFuncCallArgRectorTest
 */
final class RemoveFuncCallArgRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var RemoveFuncCallArg[]
     */
    private $removedFunctionArguments = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove argument by position by function name', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
remove_last_arg(1, 2);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
remove_last_arg(1);
CODE_SAMPLE
, [new RemoveFuncCallArg('remove_last_arg', 1)])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->name instanceof Expr) {
            return null;
        }
        $hasChanged = \false;
        foreach ($this->removedFunctionArguments as $removedFunctionArgument) {
            if (!$this->isName($node->name, $removedFunctionArgument->getFunction())) {
                continue;
            }
            foreach (array_keys($node->args) as $position) {
                if ($removedFunctionArgument->getArgumentPosition() !== $position) {
                    continue;
                }
                unset($node->args[$position]);
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsAOf($configuration, RemoveFuncCallArg::class);
        $this->removedFunctionArguments = $configuration;
    }
}
