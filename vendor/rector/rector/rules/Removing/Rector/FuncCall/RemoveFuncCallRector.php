<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\FuncCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\NodeVisitor;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\FuncCall\RemoveFuncCallRector\RemoveFuncCallRectorTest
 */
final class RemoveFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private $removedFunctions = [];
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove defined function calls', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$x = 'something';
var_dump($x);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$x = 'something';
CODE_SAMPLE
, ['var_dump'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node): ?int
    {
        $expr = $node->expr;
        if (!$expr instanceof FuncCall) {
            return null;
        }
        foreach ($this->removedFunctions as $removedFunction) {
            if (!$this->isName($expr->name, $removedFunction)) {
                continue;
            }
            return NodeVisitor::REMOVE_NODE;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allString($configuration);
        $this->removedFunctions = $configuration;
    }
}
