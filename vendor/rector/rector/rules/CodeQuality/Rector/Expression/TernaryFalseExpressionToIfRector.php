<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Expression;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Expression\TernaryFalseExpressionToIfRector\TernaryFalseExpressionToIfRectorTest
 */
final class TernaryFalseExpressionToIfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change ternary with false to if and explicit call', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value, $someMethod)
    {
        $value ? $someMethod->call($value) : false;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run($value, $someMethod)
    {
        if ($value) {
            $someMethod->call($value);
        }
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
     */
    public function refactor(Node $node): ?Node
    {
        if (!$node->expr instanceof Ternary) {
            return null;
        }
        $ternary = $node->expr;
        if (!$ternary->if instanceof Expr) {
            return null;
        }
        if (!$ternary->else instanceof Variable && $this->exprAnalyzer->isDynamicExpr($ternary->else)) {
            return null;
        }
        return new If_($ternary->cond, ['stmts' => [new Expression($ternary->if)]]);
    }
}
