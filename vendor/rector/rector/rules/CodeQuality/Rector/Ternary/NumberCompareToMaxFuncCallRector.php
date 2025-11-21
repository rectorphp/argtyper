<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Ternary;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Greater;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\GreaterOrEqual;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Smaller;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Ternary\NumberCompareToMaxFuncCallRector\NumberCompareToMaxFuncCallRectorTest
 */
final class NumberCompareToMaxFuncCallRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change ternary number compare to `max()` call', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return $value > 100 ? $value : 100;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value)
    {
        return max($value, 100);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Ternary::class];
    }
    /**
     * @param Ternary $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->cond instanceof BinaryOp) {
            return null;
        }
        $binaryOp = $node->cond;
        if (!$this->areIntegersCompared($binaryOp)) {
            return null;
        }
        if ($binaryOp instanceof Smaller || $binaryOp instanceof SmallerOrEqual) {
            if (!$this->nodeComparator->areNodesEqual($binaryOp->left, $node->else)) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($binaryOp->right, $node->if)) {
                return null;
            }
            return $this->nodeFactory->createFuncCall('max', [$node->if, $node->else]);
        }
        if ($binaryOp instanceof Greater || $binaryOp instanceof GreaterOrEqual) {
            if (!$this->nodeComparator->areNodesEqual($binaryOp->left, $node->if)) {
                return null;
            }
            if (!$this->nodeComparator->areNodesEqual($binaryOp->right, $node->else)) {
                return null;
            }
            return $this->nodeFactory->createFuncCall('max', [$node->if, $node->else]);
        }
        return null;
    }
    private function areIntegersCompared(BinaryOp $binaryOp): bool
    {
        $leftType = $this->getType($binaryOp->left);
        if (!$leftType->isInteger()->yes()) {
            return \false;
        }
        $rightType = $this->getType($binaryOp->right);
        return $rightType->isInteger()->yes();
    }
}
