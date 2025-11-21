<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\MethodCall;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\Rector\Doctrine\TypedCollections\TypeAnalyzer\CollectionTypeDetector;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\MethodCall\AssertSameCountOnCollectionToAssertCountRector\AssertSameCountOnCollectionToAssertCountRectorTest
 */
final class AssertSameCountOnCollectionToAssertCountRector extends AbstractRector
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
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $this->assertSame(5, $collection->count()) to $this->assertCount(5, $collection) in tests', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class SomeClass extends \PHPUnit\Framework\TestCase
{
    private Collection $items;

    public function test(): void
    {
        $this->assertSame(5, $this->items->count());
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
        $this->assertCount(5, $this->items);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|null
     */
    public function refactor(Node $node)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node->name, 'assertSame')) {
            return null;
        }
        if ($node instanceof MethodCall && !$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $comparedArg = $node->getArgs()[1]->value;
        if ($comparedArg instanceof MethodCall && $this->isName($comparedArg->name, 'count') && $this->collectionTypeDetector->isCollectionType($comparedArg->var)) {
            $node->name = new Identifier('assertCount');
            $node->args[1] = new Arg($comparedArg->var);
            return $node;
        }
        return null;
    }
}
