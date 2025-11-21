<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticPropertyFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Rector\NodeManipulator\IfManipulator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\RemoveDeadInstanceOfRector\RemoveDeadInstanceOfRectorTest
 */
final class RemoveDeadInstanceOfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(IfManipulator $ifManipulator, ReflectionResolver $reflectionResolver)
    {
        $this->ifManipulator = $ifManipulator;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove dead instanceof check on type hinted variable', [new CodeSample(<<<'CODE_SAMPLE'
function run(stdClass $stdClass)
{
    if (! $stdClass instanceof stdClass) {
        return false;
    }

    return true;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run(stdClass $stdClass)
{
    return true;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }
    /**
     * @param If_ $node
     * @return Stmt[]|null|int|If_
     */
    public function refactor(Node $node)
    {
        if (!$this->ifManipulator->isIfWithoutElseAndElseIfs($node)) {
            return null;
        }
        if ($node->cond instanceof BooleanNot && $node->cond->expr instanceof Instanceof_) {
            return $this->refactorStmtAndInstanceof($node, $node->cond->expr);
        }
        if ($node->cond instanceof BooleanAnd) {
            return $this->refactorIfWithBooleanAnd($node);
        }
        if ($node->cond instanceof Instanceof_) {
            return $this->refactorStmtAndInstanceof($node, $node->cond);
        }
        return null;
    }
    /**
     * @return null|Stmt[]|int
     */
    private function refactorStmtAndInstanceof(If_ $if, Instanceof_ $instanceof)
    {
        if ($this->isInstanceofTheSameType($instanceof) !== \true) {
            return null;
        }
        if ($this->shouldSkipFromNotTypedParam($instanceof)) {
            return null;
        }
        if ($instanceof->expr instanceof Assign) {
            $instanceof->expr->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \false);
            $assignExpression = new Expression($instanceof->expr);
            return array_merge([$assignExpression], $if->stmts);
        }
        if ($if->cond !== $instanceof) {
            return NodeVisitor::REMOVE_NODE;
        }
        if ($if->stmts === []) {
            return NodeVisitor::REMOVE_NODE;
        }
        // unwrap stmts
        return $if->stmts;
    }
    private function shouldSkipFromNotTypedParam(Instanceof_ $instanceof): bool
    {
        $nativeParamType = $this->nodeTypeResolver->getNativeType($instanceof->expr);
        return $nativeParamType instanceof MixedType;
    }
    private function isPropertyFetch(Expr $expr): bool
    {
        if ($expr instanceof PropertyFetch) {
            return \true;
        }
        return $expr instanceof StaticPropertyFetch;
    }
    private function isInstanceofTheSameType(Instanceof_ $instanceof): ?bool
    {
        if (!$instanceof->class instanceof Name) {
            return null;
        }
        // handled in another rule
        if ($this->isPropertyFetch($instanceof->expr) || $instanceof->expr instanceof CallLike) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassReflection($instanceof);
        if ($classReflection instanceof ClassReflection && $classReflection->isTrait()) {
            return null;
        }
        $exprType = $this->nodeTypeResolver->getNativeType($instanceof->expr);
        if (!$exprType instanceof ObjectType) {
            return null;
        }
        $className = $instanceof->class->toString();
        return $exprType->isInstanceOf($className)->yes();
    }
    private function refactorIfWithBooleanAnd(If_ $if): ?\Argtyper202511\PhpParser\Node\Stmt\If_
    {
        if (!$if->cond instanceof BooleanAnd) {
            return null;
        }
        $booleanAnd = $if->cond;
        if (!$booleanAnd->left instanceof Instanceof_) {
            return null;
        }
        $instanceof = $booleanAnd->left;
        if ($this->isInstanceofTheSameType($instanceof) !== \true) {
            return null;
        }
        $if->cond = $booleanAnd->right;
        return $if;
    }
}
