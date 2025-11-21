<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class MethodUniqueReturnedPropertyResolver
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function resolve(Class_ $class, ClassMethod $classMethod): ?Property
    {
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        if (\count($returns) !== 1) {
            return null;
        }
        $return = \reset($returns);
        $returnExpr = $return->expr;
        if (!$returnExpr instanceof PropertyFetch) {
            return null;
        }
        $propertyName = (string) $this->nodeNameResolver->getName($returnExpr);
        return $class->getProperty($propertyName);
    }
}
