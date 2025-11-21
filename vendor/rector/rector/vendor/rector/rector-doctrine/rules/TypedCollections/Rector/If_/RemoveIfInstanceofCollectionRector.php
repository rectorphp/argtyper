<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Coalesce;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Instanceof_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Rector\Doctrine\Enum\TestClass;
use Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\If_\RemoveIfInstanceofCollectionRector\RemoveIfInstanceofCollectionRectorTest
 */
final class RemoveIfInstanceofCollectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector
     */
    private $collectionTypeDetector;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(CollectionTypeDetector $collectionTypeDetector, ValueResolver $valueResolver, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->collectionTypeDetector = $collectionTypeDetector;
        $this->valueResolver = $valueResolver;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getNodeTypes(): array
    {
        return [If_::class, Ternary::class, Coalesce::class, BooleanAnd::class, BooleanNot::class];
    }
    /**
     * @param If_|Ternary|Coalesce|BooleanAnd|BooleanNot $node
     * @return Node|Node[]|int|null
     */
    public function refactor(Node $node)
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($node instanceof BooleanNot) {
            if ($this->collectionTypeDetector->isCollectionType($node->expr)) {
                return new MethodCall($node->expr, 'isEmpty');
            }
            return null;
        }
        if ($node instanceof BooleanAnd) {
            if ($this->isInstanceofCollectionType($node->left)) {
                return $node->right;
            }
            if ($this->isInstanceofCollectionType($node->right)) {
                return $node->left;
            }
            return null;
        }
        if ($node instanceof Coalesce) {
            if ($this->collectionTypeDetector->isCollectionType($node->left)) {
                return $node->left;
            }
            return null;
        }
        if ($node instanceof If_) {
            return $this->refactorIf($node);
        }
        return $this->refactorTernary($node);
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove if instance of collection on already known Collection type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    public ?Collection $items;

    public function someMethod()
    {
        if ($this->items instanceof Collection) {
            $values = $this->items;
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    public ?Collection $items;

    public function someMethod()
    {
        $values = $this->items;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return Node[]|int|Node|null
     */
    private function refactorIf(If_ $if)
    {
        if ($if->cond instanceof BooleanNot) {
            $condition = $if->cond->expr;
            if (!$condition instanceof Instanceof_) {
                return null;
            }
            if (!$this->collectionTypeDetector->isCollectionType($condition->expr)) {
                return null;
            }
            return NodeVisitorAbstract::REMOVE_NODE;
        }
        if ($if->cond instanceof Identical) {
            $identical = $if->cond;
            if ($this->valueResolver->isValue($identical->right, \false)) {
                if (!$this->isInstanceofCollectionType($identical->left)) {
                    return null;
                }
                return NodeVisitorAbstract::REMOVE_NODE;
            }
            if ($this->isName($identical->right, 'null')) {
                if ($this->collectionTypeDetector->isCollectionType($identical->left)) {
                    $if->cond = new MethodCall($if->cond->left, 'isEmpty');
                    return $if;
                }
                return null;
            }
        }
        // implicit instance of
        if ($if->cond instanceof PropertyFetch && $this->collectionTypeDetector->isCollectionType($if->cond)) {
            return $if->stmts;
        }
        if (!$this->isInstanceofCollectionType($if->cond)) {
            return null;
        }
        return $if->stmts;
    }
    private function refactorTernary(Ternary $ternary): ?Expr
    {
        $isNegated = \false;
        if ($this->isInstanceofCollectionType($ternary->cond)) {
            return $ternary->if;
        }
        if ($ternary->cond instanceof Identical && $this->isName($ternary->cond->right, 'false')) {
            $isNegated = \true;
            $condition = $ternary->cond->left;
        } else {
            $condition = $ternary->cond;
        }
        if ($this->isIsObjectFuncCallOnCollection($condition)) {
            return $ternary->if;
        }
        return null;
    }
    private function isInstanceofCollectionType(Expr $expr): bool
    {
        if (!$expr instanceof Instanceof_) {
            return \false;
        }
        return $this->collectionTypeDetector->isCollectionType($expr->expr);
    }
    private function isIsObjectFuncCallOnCollection(Expr $expr): bool
    {
        if (!$expr instanceof FuncCall) {
            return \false;
        }
        if ($expr->isFirstClassCallable()) {
            return \false;
        }
        if (!$this->isName($expr->name, 'is_object')) {
            return \false;
        }
        $firstArg = $expr->getArgs()[0];
        return $this->collectionTypeDetector->isCollectionType($firstArg->value);
    }
    /**
     * @param \PhpParser\Node\Stmt\If_|\PhpParser\Node|\PhpParser\Node\Expr\BinaryOp\Coalesce|\PhpParser\Node\Expr\Ternary|\PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\BinaryOp\BooleanAnd $node
     */
    private function shouldSkip($node): bool
    {
        // most likely on purpose in tests
        if ($this->testsNodeAnalyzer->isInTestClass($node)) {
            return \true;
        }
        $classScope = ScopeFetcher::fetch($node);
        $classReflection = $classScope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        // usually assert on purpose
        return $classReflection->is(TestClass::BEHAT_CONTEXT);
    }
}
