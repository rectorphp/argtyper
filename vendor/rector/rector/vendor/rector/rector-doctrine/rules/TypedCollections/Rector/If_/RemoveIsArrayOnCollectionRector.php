<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\If_\RemoveIsArrayOnCollectionRector\RemoveIsArrayOnCollectionRectorTest
 */
final class RemoveIsArrayOnCollectionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector
     */
    private $collectionTypeDetector;
    public function __construct(CollectionTypeDetector $collectionTypeDetector)
    {
        $this->collectionTypeDetector = $collectionTypeDetector;
    }
    public function getNodeTypes(): array
    {
        return [If_::class, Ternary::class];
    }
    /**
     * @param If_|Ternary $node
     * @return Node|Node[]|int|null
     */
    public function refactor(Node $node)
    {
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
        $items = is_array($this->items) ? $this->items : $this->items->toArray();
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
        $items = $this->items->toArray();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return Node[]|int|null
     */
    private function refactorIf(If_ $if)
    {
        if ($if->cond instanceof BooleanNot) {
            $condition = $if->cond->expr;
            if ($condition instanceof FuncCall && $this->isName($condition, 'is_array') && $this->collectionTypeDetector->isCollectionType($condition->getArgs()[0]->value)) {
                return $if->stmts;
            }
            return null;
        }
        if ($if->cond instanceof FuncCall && $this->isName($if->cond, 'is_array')) {
            $firstArg = $if->cond->getArgs()[0];
            if (!$this->collectionTypeDetector->isCollectionType($firstArg->value)) {
                return null;
            }
            return NodeVisitorAbstract::REMOVE_NODE;
        }
        return null;
    }
    private function refactorTernary(Ternary $ternary): ?Expr
    {
        $isNegated = \false;
        if ($ternary->cond instanceof Identical && $this->isName($ternary->cond->right, 'false')) {
            $isNegated = \true;
            $condition = $ternary->cond->left;
        } else {
            $condition = $ternary->cond;
        }
        if (!$condition instanceof FuncCall) {
            return null;
        }
        $funcCall = $condition;
        if ($this->isName($funcCall, 'is_array')) {
            $firstArg = $funcCall->getArgs()[0];
            if (!$this->collectionTypeDetector->isCollectionType($firstArg->value)) {
                return null;
            }
            if ($isNegated) {
                return $ternary->if;
            }
            return $ternary->else;
        }
        return null;
    }
}
