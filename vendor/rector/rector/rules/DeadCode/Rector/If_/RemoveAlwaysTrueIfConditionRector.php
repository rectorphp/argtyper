<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\Rector\DeadCode\NodeAnalyzer\SafeLeftTypeBooleanAndOrAnalyzer;
use Argtyper202511\Rector\NodeAnalyzer\ExprAnalyzer;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector\RemoveAlwaysTrueIfConditionRectorTest
 */
final class RemoveAlwaysTrueIfConditionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ExprAnalyzer
     */
    private $exprAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\DeadCode\NodeAnalyzer\SafeLeftTypeBooleanAndOrAnalyzer
     */
    private $safeLeftTypeBooleanAndOrAnalyzer;
    public function __construct(ExprAnalyzer $exprAnalyzer, BetterNodeFinder $betterNodeFinder, SafeLeftTypeBooleanAndOrAnalyzer $safeLeftTypeBooleanAndOrAnalyzer)
    {
        $this->exprAnalyzer = $exprAnalyzer;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->safeLeftTypeBooleanAndOrAnalyzer = $safeLeftTypeBooleanAndOrAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove if condition that is always true', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        if (1 === 1) {
            return 'yes';
        }

        return 'no';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function go()
    {
        return 'yes';

        return 'no';
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return int|null|Stmt[]|If_
     */
    public function refactor(Node $node)
    {
        if ($node->cond instanceof BooleanAnd) {
            return $this->refactorIfWithBooleanAnd($node);
        }
        if ($node->else instanceof Else_) {
            return null;
        }
        // just one if
        if ($node->elseifs !== []) {
            return null;
        }
        $conditionStaticType = $this->nodeTypeResolver->getNativeType($node->cond);
        if (!$conditionStaticType->isTrue()->yes()) {
            return null;
        }
        if ($this->shouldSkipExpr($node->cond)) {
            return null;
        }
        if ($this->shouldSkipFromVariable($node->cond)) {
            return null;
        }
        $hasAssign = (bool) $this->betterNodeFinder->findFirstInstanceOf($node->cond, Assign::class);
        if ($hasAssign) {
            return null;
        }
        $type = ScopeFetcher::fetch($node)->getNativeType($node->cond);
        if (!$type->isTrue()->yes()) {
            return null;
        }
        if ($node->stmts === []) {
            return NodeVisitor::REMOVE_NODE;
        }
        $node->stmts[0]->setAttribute(AttributeKey::COMMENTS, \array_merge($node->getComments(), $node->stmts[0]->getComments()));
        $node->stmts[0]->setAttribute(AttributeKey::HAS_MERGED_COMMENTS, \true);
        return $node->stmts;
    }
    private function shouldSkipFromVariable(Expr $expr) : bool
    {
        /** @var Variable[] $variables */
        $variables = $this->betterNodeFinder->findInstancesOf($expr, [Variable::class]);
        foreach ($variables as $variable) {
            if ($this->exprAnalyzer->isNonTypedFromParam($variable)) {
                return \true;
            }
            $type = $this->nodeTypeResolver->getNativeType($variable);
            if ($type instanceof IntersectionType) {
                foreach ($type->getTypes() as $subType) {
                    if ($subType->isArray()->yes()) {
                        return \true;
                    }
                }
            }
        }
        return \false;
    }
    private function shouldSkipExpr(Expr $expr) : bool
    {
        return (bool) $this->betterNodeFinder->findInstancesOf($expr, [PropertyFetch::class, StaticPropertyFetch::class, ArrayDimFetch::class, MethodCall::class, StaticCall::class]);
    }
    private function refactorIfWithBooleanAnd(If_ $if) : ?If_
    {
        if (!$if->cond instanceof BooleanAnd) {
            return null;
        }
        $booleanAnd = $if->cond;
        $leftType = $this->getType($booleanAnd->left);
        if (!$leftType->isTrue()->yes()) {
            return null;
        }
        if (!$this->safeLeftTypeBooleanAndOrAnalyzer->isSafe($booleanAnd)) {
            return null;
        }
        $if->cond = $booleanAnd->right;
        return $if;
    }
}
