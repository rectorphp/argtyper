<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\Assign;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\Assign\ArrayDimFetchAssignToAddCollectionCallRector\ArrayDimFetchAssignToAddCollectionCallRectorTest
 */
final class ArrayDimFetchAssignToAddCollectionCallRector extends AbstractRector
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
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change add dim assign on collection to an->add() call', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SetFirstParameterArray
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function setItems($item)
    {
        $this->items[] = $item;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SetFirstParameterArray
{
    /**
     * @var Collection<int, string>
     */
    public $items;

    public function setItems($item)
    {
        $this->items->add($item);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?MethodCall
    {
        if (!$node->var instanceof ArrayDimFetch) {
            return null;
        }
        $arrayDimFetch = $node->var;
        if ($arrayDimFetch->dim instanceof Expr) {
            return null;
        }
        $assignedExpr = $arrayDimFetch->var;
        if (!$this->collectionTypeDetector->isCollectionType($assignedExpr)) {
            return null;
        }
        return new MethodCall($assignedExpr, 'add', [new Arg($node->expr)]);
    }
}
