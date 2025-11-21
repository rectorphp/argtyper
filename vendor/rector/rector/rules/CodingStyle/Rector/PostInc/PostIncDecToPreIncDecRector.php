<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\PostInc;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\PostDec;
use Argtyper202511\PhpParser\Node\Expr\PostInc;
use Argtyper202511\PhpParser\Node\Expr\PreDec;
use Argtyper202511\PhpParser\Node\Expr\PreInc;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\For_;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector\PostIncDecToPreIncDecRectorTest
 */
final class PostIncDecToPreIncDecRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use ++$value or --$value  instead of `$value++` or `$value--`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value = 1)
    {
        $value++; echo $value;
        $value--; echo $value;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($value = 1)
    {
        ++$value; echo $value;
        --$value; echo $value;
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
        return [For_::class, Expression::class];
    }
    /**
     * @param For_|Expression $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Expression) {
            return $this->refactorExpression($node);
        }
        return $this->refactorFor($node);
    }
    private function refactorFor(For_ $for): ?\Argtyper202511\PhpParser\Node\Stmt\For_
    {
        if (count($for->loop) !== 1) {
            return null;
        }
        $singleLoopExpr = $for->loop[0];
        if (!$singleLoopExpr instanceof PostInc && !$singleLoopExpr instanceof PostDec) {
            return null;
        }
        $for->loop = [$this->processPrePost($singleLoopExpr)];
        return $for;
    }
    /**
     * @param \PhpParser\Node\Expr\PostInc|\PhpParser\Node\Expr\PostDec $node
     * @return \PhpParser\Node\Expr\PreInc|\PhpParser\Node\Expr\PreDec
     */
    private function processPrePost($node)
    {
        if ($node instanceof PostInc) {
            return new PreInc($node->var);
        }
        return new PreDec($node->var);
    }
    private function refactorExpression(Expression $expression): ?Expression
    {
        if ($expression->expr instanceof PostInc || $expression->expr instanceof PostDec) {
            $expression->expr = $this->processPrePost($expression->expr);
            return $expression;
        }
        return null;
    }
}
