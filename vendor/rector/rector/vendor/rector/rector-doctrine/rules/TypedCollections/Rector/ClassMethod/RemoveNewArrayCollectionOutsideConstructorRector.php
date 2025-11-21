<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\RemoveNewArrayCollectionOutsideConstructorRector\RemoveNewArrayCollectionOutsideConstructorRectorTest
 */
final class RemoveNewArrayCollectionOutsideConstructorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove new ArrayCollection() assigns outside constructor', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class NoAssignOutsideConstructor
{
    public Collection $items;

    public function anotherMethod()
    {
        $this->items = new ArrayCollection();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\RemoveNewArrayCollectionOutsideConstructorRector\Fixture;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class NoAssignOutsideConstructor
{
    public Collection $items;

    public function anotherMethod()
    {
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        if ($node->isAbstract()) {
            return null;
        }
        if ($this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        if ($this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $methodName = $this->getName($node);
        if (strpos($methodName, 'remove') !== \false || strpos($methodName, 'clear') !== \false) {
            return null;
        }
        foreach ((array) $node->stmts as $key => $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            // only assign of new ArrayCollection
            if (!$stmt->expr instanceof Assign) {
                continue;
            }
            /** @var Assign $assign */
            $assign = $stmt->expr;
            // we only care about initialization
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            if (!$assign->expr instanceof New_) {
                continue;
            }
            // skip if has some values
            if ($assign->expr->getArgs() !== []) {
                continue;
            }
            $new = $assign->expr;
            if (!$this->isName($new->class, DoctrineClass::ARRAY_COLLECTION)) {
                continue;
            }
            unset($node->stmts[$key]);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
