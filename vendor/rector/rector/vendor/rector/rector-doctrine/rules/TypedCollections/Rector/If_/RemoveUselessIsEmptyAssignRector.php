<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\If_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\If_\RemoveUselessIsEmptyAssignRector\RemoveUselessIsEmptyAssignRectorTest
 */
final class RemoveUselessIsEmptyAssignRector extends AbstractRector
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
        return [If_::class];
    }
    /**
     * @param If_ $node
     */
    public function refactor(Node $node): ?int
    {
        if (!$node->cond instanceof MethodCall) {
            return null;
        }
        $methodCall = $node->cond;
        if (!$this->isName($methodCall->name, 'isEmpty')) {
            return null;
        }
        if (!$this->collectionTypeDetector->isCollectionType($methodCall->var)) {
            return null;
        }
        if (count($node->stmts) !== 1) {
            return null;
        }
        $soleStmts = $node->stmts[0];
        if (!$soleStmts instanceof Expression) {
            return null;
        }
        if (!$soleStmts->expr instanceof Assign) {
            return null;
        }
        $assign = $soleStmts->expr;
        if (!$this->nodeNameResolver->areNamesEqual($assign->var, $methodCall->var)) {
            return null;
        }
        if (!$this->isArrayCollectionNewInstance($assign->expr)) {
            return null;
        }
        return NodeVisitor::REMOVE_NODE;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove useless isEmpty() check on collection with following new ArrayCollection() instance', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private Collection $collection;

    public function someMethod()
    {
        if ($this->collection->isEmpty()) {
            $this->collection = new ArrayCollection();
        }

        return $this->collection;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass
{
    private Collection $collection;

    public function someMethod()
    {
        return $this->collection;
    }
}
CODE_SAMPLE
)]);
    }
    private function isArrayCollectionNewInstance(Expr $expr): bool
    {
        if (!$expr instanceof New_) {
            return \true;
        }
        return $this->isName($expr->class, DoctrineClass::ARRAY_COLLECTION);
    }
}
