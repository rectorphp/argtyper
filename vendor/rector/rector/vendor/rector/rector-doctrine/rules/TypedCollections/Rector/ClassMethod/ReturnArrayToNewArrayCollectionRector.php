<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\TypedCollections\Rector\ClassMethod;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\TypedCollections\Rector\ClassMethod\ReturnArrayToNewArrayCollectionRector\ReturnArrayToNewArrayCollectionRectorTest
 */
final class ReturnArrayToNewArrayCollectionRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change return [] to return new ArrayCollection([]) in a method, that returns Collection type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Collections\Collection;

final class ReturnArrayItems
{
    public function getItems(): Collection
    {
        $items = [1, 2, 3];
        $items[] = 4;

        return $items;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

final class ReturnArrayItems
{
    public function getItems(): Collection
    {
        $items = [1, 2, 3];
        $items[] = 4;

        return new ArrayCollection($items);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?ClassMethod
    {
        if (!$node->returnType instanceof Name) {
            return null;
        }
        if (!$this->isName($node->returnType, DoctrineClass::COLLECTION)) {
            return null;
        }
        // update all return [] to return new ArrayCollection([])
        $hasChanged = \false;
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node) use(&$hasChanged) : ?Node {
            if (!$node instanceof Return_) {
                return null;
            }
            if (!$node->expr instanceof Expr) {
                return null;
            }
            $exprType = $this->getType($node->expr);
            if (!$exprType->isArray()->yes()) {
                return null;
            }
            $node->expr = new New_(new FullyQualified(DoctrineClass::ARRAY_COLLECTION), [new Arg($node->expr)]);
            $hasChanged = \true;
            return $node;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
