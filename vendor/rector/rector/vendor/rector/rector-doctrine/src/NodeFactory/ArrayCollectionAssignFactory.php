<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeFactory;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class ArrayCollectionAssignFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    public function createFromPropertyName(string $toManyPropertyName): Expression
    {
        $propertyFetch = $this->nodeFactory->createPropertyFetch('this', $toManyPropertyName);
        $new = new New_(new FullyQualified('Argtyper202511\Doctrine\Common\Collections\ArrayCollection'));
        $assign = new Assign($propertyFetch, $new);
        return new Expression($assign);
    }
}
