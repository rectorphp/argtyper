<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\Rector\Assign;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\Assign\SplitDoubleAssignRector\SplitDoubleAssignRectorTest
 */
final class SplitDoubleAssignRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Split multiple inline assigns to each own lines default value, to prevent undefined array issues', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = $two = 1;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $one = 1;
        $two = 1;
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Expression[]|null
     */
    public function refactor(Node $node): ?array
    {
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $firstAssign = $node->expr;
        if (!$firstAssign->expr instanceof Assign) {
            return null;
        }
        $expr = $this->resolveLastAssignExpr($firstAssign);
        $collectExpressions = $this->collectExpressions($firstAssign, $expr);
        if ($collectExpressions === []) {
            return null;
        }
        return $collectExpressions;
    }
    /**
     * @return Expression[]
     */
    private function collectExpressions(Assign $assign, Expr $expr): array
    {
        /** @var Expression[] $expressions */
        $expressions = [];
        while ($assign instanceof Assign) {
            if ($assign->var instanceof ArrayDimFetch) {
                return [];
            }
            $expressions[] = new Expression(new Assign($assign->var, $expr));
            // CallLike check need to be after first fill Expression
            // so use existing variable defined to avoid repetitive call
            if ($expr instanceof CallLike) {
                $expr = $assign->var;
            }
            $assign = $assign->expr;
        }
        return $expressions;
    }
    private function resolveLastAssignExpr(Assign $assign): Expr
    {
        if (!$assign->expr instanceof Assign) {
            return $assign->expr;
        }
        return $this->resolveLastAssignExpr($assign->expr);
    }
}
