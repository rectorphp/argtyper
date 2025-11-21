<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\For_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Smaller;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\SmallerOrEqual;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\For_;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\For_\ForRepeatedCountToOwnVariableRector\ForRepeatedCountToOwnVariableRectorTest
 */
final class ForRepeatedCountToOwnVariableRector extends AbstractRector
{
    /**
     * @var string
     */
    private const COUNTER_NAME = 'counter';
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change count() in for function to own variable', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        for ($i = 5; $i <= count($items); $i++) {
            echo $items[$i];
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $itemsCount = count($items);
        for ($i = 5; $i <= $itemsCount; $i++) {
            echo $items[$i];
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [For_::class];
    }
    /**
     * @param For_ $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node) : ?array
    {
        $scope = ScopeFetcher::fetch($node);
        if ($scope->hasVariableType(self::COUNTER_NAME)->yes()) {
            return null;
        }
        $countInCond = null;
        $counterVariable = new Variable(self::COUNTER_NAME);
        foreach ($node->cond as $condExpr) {
            if (!$condExpr instanceof Smaller && !$condExpr instanceof SmallerOrEqual) {
                continue;
            }
            if (!$condExpr->right instanceof FuncCall) {
                continue;
            }
            $funcCall = $condExpr->right;
            if (!$this->isName($funcCall, 'count')) {
                continue;
            }
            $countInCond = $condExpr->right;
            $condExpr->right = $counterVariable;
        }
        if (!$countInCond instanceof Expr) {
            return null;
        }
        $countAssign = new Assign($counterVariable, $countInCond);
        return [new Expression($countAssign), $node];
    }
}
