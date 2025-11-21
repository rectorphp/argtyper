<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\ValueObject\MethodName;
final class ConstructorAssignPropertyAnalyzer
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
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeNameResolver $nodeNameResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function resolveConstructorAssign(Class_ $class, Property $property) : ?Node
    {
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            return null;
        }
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        return $this->betterNodeFinder->findFirst((array) $constructClassMethod->stmts, function (Node $node) use($propertyName) : bool {
            if (!$node instanceof Assign) {
                return \false;
            }
            return $this->propertyFetchAnalyzer->isLocalPropertyFetchName($node->var, $propertyName);
        });
    }
}
