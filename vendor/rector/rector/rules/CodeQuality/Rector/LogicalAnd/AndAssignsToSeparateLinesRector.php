<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\LogicalAnd;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\LogicalAnd;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\LogicalAnd\AndAssignsToSeparateLinesRector\AndAssignsToSeparateLinesRectorTest
 */
final class AndAssignsToSeparateLinesRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Split 2 assigns with ands to separate line', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4 and $tokens[] = $token;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $tokens = [];
        $token = 4;
        $tokens[] = $token;
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Expression[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if (!$node->expr instanceof LogicalAnd) {
            return null;
        }
        $logicalAnd = $node->expr;
        if (!$logicalAnd->left instanceof Assign) {
            return null;
        }
        if (!$logicalAnd->right instanceof Assign) {
            return null;
        }
        $leftAssignExpression = new Expression($logicalAnd->left);
        $rightAssignExpression = new Expression($logicalAnd->right);
        return [$leftAssignExpression, $rightAssignExpression];
    }
}
