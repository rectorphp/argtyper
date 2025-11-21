<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeManipulator;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\AssignOp;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\ValueObject\MethodName;
final class PropertyFetchAssignManipulator
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
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
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }
    public function isAssignedMultipleTimesInConstructor(Class_ $class, Property $property) : bool
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod) {
            return \false;
        }
        $count = 0;
        $propertyName = $this->nodeNameResolver->getName($property);
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use($propertyName, &$count) : ?int {
            // skip anonymous classes and inner function
            if ($node instanceof Class_ || $node instanceof Function_) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Assign && !$node instanceof AssignOp) {
                return null;
            }
            if (!$this->propertyFetchAnalyzer->isLocalPropertyFetchName($node->var, $propertyName)) {
                return null;
            }
            ++$count;
            if ($count === 2) {
                return NodeVisitor::STOP_TRAVERSAL;
            }
            return null;
        });
        return $count === 2;
    }
}
