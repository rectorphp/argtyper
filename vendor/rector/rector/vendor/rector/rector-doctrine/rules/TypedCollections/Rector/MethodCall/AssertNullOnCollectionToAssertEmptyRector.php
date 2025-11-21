<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\MethodCall\AssertNullOnCollectionToAssertEmptyRector\AssertNullOnCollectionToAssertEmptyRectorTest
 */
final class AssertNullOnCollectionToAssertEmptyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector
     */
    private $collectionTypeDetector;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, CollectionTypeDetector $collectionTypeDetector)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->collectionTypeDetector = $collectionTypeDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this->assertNull() on Collection object to $this->assertEmpty() in tests', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass extends \PHPUnit\Framework\TestCase
{
    private Collection $items;

    public function test(): void
    {
        $this->assertNull($this->items);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass extends \PHPUnit\Framework\TestCase
{
    private Collection $items;

    public function test(): void
    {
        $this->assertEmpty($this->items);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?\Argtyper202511\PhpParser\Node\Expr\MethodCall
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'assertNull')) {
            return null;
        }
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if (!$this->collectionTypeDetector->isCollectionType($firstArg->value)) {
            return null;
        }
        // rename
        $node->name = new Identifier('assertEmpty');
        return $node;
    }
}
