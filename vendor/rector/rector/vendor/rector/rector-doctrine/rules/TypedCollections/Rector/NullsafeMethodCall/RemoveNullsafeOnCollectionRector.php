<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\NullsafeMethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\NullsafeMethodCall;
use Argtyper202511\Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\NullsafeMethodCall\RemoveNullsafeOnCollectionRector\RemoveNullsafeOnCollectionRectorTest
 */
final class RemoveNullsafeOnCollectionRector extends AbstractRector
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
        return new RuleDefinition('Remove nullsafe check on method call on a Collection type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

class SomeClass
{
    private Collection $collection;

    public function run()
    {
        return $this->collection?->empty();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

class SomeClass
{
    private Collection $collection;

    public function run()
    {
        return $this->collection->empty();
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [NullsafeMethodCall::class];
    }
    /**
     * @param NullsafeMethodCall $node
     */
    public function refactor(Node $node): ?MethodCall
    {
        if (!$this->collectionTypeDetector->isCollectionNonNullableType($node->var)) {
            return null;
        }
        return new MethodCall($node->var, $node->name, $node->args);
    }
}
