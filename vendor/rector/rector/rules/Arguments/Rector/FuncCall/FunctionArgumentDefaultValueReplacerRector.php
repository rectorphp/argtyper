<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Arguments\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\Rector\Arguments\ArgumentDefaultValueReplacer;
use Argtyper202511\Rector\Arguments\ValueObject\ReplaceFuncCallArgumentDefaultValue;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Arguments\Rector\FuncCall\FunctionArgumentDefaultValueReplacerRector\FunctionArgumentDefaultValueReplacerRectorTest
 */
final class FunctionArgumentDefaultValueReplacerRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Arguments\ArgumentDefaultValueReplacer
     */
    private $argumentDefaultValueReplacer;
    /**
     * @var ReplaceFuncCallArgumentDefaultValue[]
     */
    private $replacedArguments = [];
    public function __construct(ArgumentDefaultValueReplacer $argumentDefaultValueReplacer)
    {
        $this->argumentDefaultValueReplacer = $argumentDefaultValueReplacer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Streamline the operator arguments of `version_compare` function', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'gte');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
version_compare(PHP_VERSION, '5.6', 'ge');
CODE_SAMPLE
, [new ReplaceFuncCallArgumentDefaultValue('version_compare', 2, 'gte', 'ge')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?\Argtyper202511\PhpParser\Node\Expr\FuncCall
    {
        $hasChanged = \false;
        foreach ($this->replacedArguments as $replacedArgument) {
            if (!$this->isName($node->name, $replacedArgument->getFunction())) {
                continue;
            }
            $changedNode = $this->argumentDefaultValueReplacer->processReplaces($node, $replacedArgument);
            if ($changedNode instanceof Node) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, ReplaceFuncCallArgumentDefaultValue::class);
        $this->replacedArguments = $configuration;
    }
}
