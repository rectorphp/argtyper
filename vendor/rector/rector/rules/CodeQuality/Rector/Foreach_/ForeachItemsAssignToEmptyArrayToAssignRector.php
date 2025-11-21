<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Foreach_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PhpParser\NodeVisitor;
use Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer;
use Rector\Contract\PhpParser\Node\StmtsAwareInterface;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Foreach_\ForeachItemsAssignToEmptyArrayToAssignRector\ForeachItemsAssignToEmptyArrayToAssignRectorTest
 */
final class ForeachItemsAssignToEmptyArrayToAssignRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeAnalyzer\ForeachAnalyzer
     */
    private $foreachAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    public function __construct(ForeachAnalyzer $foreachAnalyzer, ValueResolver $valueResolver, ExprAnalyzer $exprAnalyzer)
    {
        $this->foreachAnalyzer = $foreachAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->exprAnalyzer = $exprAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change foreach() items assign to empty array to direct assign', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        foreach ($items as $item) {
             $collectedItems[] = $item;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($items)
    {
        $collectedItems = [];

        $collectedItems = $items;
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
        return [StmtsAwareInterface::class];
    }
    /**
     * @param StmtsAwareInterface $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->stmts === null) {
            return null;
        }
        $emptyArrayVariables = [];
        foreach ($node->stmts as $key => $stmt) {
            $variableName = $this->matchEmptyArrayVariableAssign($stmt);
            if (is_string($variableName)) {
                $emptyArrayVariables[] = $variableName;
            }
            if (!$stmt instanceof Foreach_) {
                if ($this->isAppend($stmt, $emptyArrayVariables)) {
                    return null;
                }
                continue;
            }
            if ($this->shouldSkip($stmt, $emptyArrayVariables)) {
                continue;
            }
            $assignVariable = $this->foreachAnalyzer->matchAssignItemsOnlyForeachArrayVariable($stmt);
            if (!$assignVariable instanceof Expr) {
                continue;
            }
            $directAssign = new Assign($assignVariable, $stmt->expr);
            $node->stmts[$key] = new Expression($directAssign);
            return $node;
        }
        return null;
    }
    /**
     * @param string[] $emptyArrayVariables
     */
    private function isAppend(Stmt $stmt, array $emptyArrayVariables): bool
    {
        $isAppend = \false;
        $this->traverseNodesWithCallable($stmt, function (Node $subNode) use ($emptyArrayVariables, &$isAppend): ?int {
            if ($subNode instanceof Assign && $subNode->var instanceof ArrayDimFetch) {
                $isAppend = $this->isNames($subNode->var->var, $emptyArrayVariables);
                if ($isAppend) {
                    return NodeVisitor::STOP_TRAVERSAL;
                }
            }
            if ($subNode instanceof Assign && $subNode->var instanceof Variable && $this->isNames($subNode->var, $emptyArrayVariables) && !$this->valueResolver->isValue($subNode->expr, [])) {
                $isAppend = \true;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            return null;
        });
        return $isAppend;
    }
    /**
     * @param string[] $emptyArrayVariables
     */
    private function shouldSkip(Foreach_ $foreach, array $emptyArrayVariables): bool
    {
        $assignVariableExpr = $this->foreachAnalyzer->matchAssignItemsOnlyForeachArrayVariable($foreach);
        if (!$assignVariableExpr instanceof Expr) {
            return \true;
        }
        $foreachedExprType = $this->nodeTypeResolver->getNativeType($foreach->expr);
        // only arrays, not traversable/iterable
        if (!$foreachedExprType->isArray()->yes()) {
            return \true;
        }
        return !$this->isNames($assignVariableExpr, $emptyArrayVariables);
    }
    private function matchEmptyArrayVariableAssign(Stmt $stmt): ?string
    {
        if (!$stmt instanceof Expression) {
            return null;
        }
        if (!$stmt->expr instanceof Assign) {
            return null;
        }
        $assign = $stmt->expr;
        if (!$assign->var instanceof Variable) {
            return null;
        }
        if (!$assign->expr instanceof Array_) {
            return null;
        }
        // must be assign of empty array
        if (!$this->valueResolver->isValue($assign->expr, [])) {
            return null;
        }
        if ($this->exprAnalyzer->isDynamicArray($assign->expr)) {
            return null;
        }
        return $this->getName($assign->var);
    }
}
