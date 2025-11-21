<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class MockedVariableAnalyzer
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
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function containsMockAsUsedVariable(ClassMethod $classMethod): bool
    {
        $doesContainMock = \false;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod, function (Node $node) use (&$doesContainMock) {
            if ($this->isMockeryStaticCall($node)) {
                $doesContainMock = \true;
                return null;
            }
            if (!$node instanceof PropertyFetch && !$node instanceof Variable) {
                return null;
            }
            $variableType = $this->nodeTypeResolver->getType($node);
            if ($variableType instanceof MixedType) {
                return null;
            }
            if ($this->isIntersectionTypeWithMockObject($variableType)) {
                $doesContainMock = \true;
            }
            if ($variableType->isSuperTypeOf(new ObjectType('Argtyper202511\PHPUnit\Framework\MockObject\MockObject'))->yes()) {
                $doesContainMock = \true;
            }
            return null;
        });
        return $doesContainMock;
    }
    private function isIntersectionTypeWithMockObject(Type $variableType): bool
    {
        if ($variableType instanceof IntersectionType) {
            foreach ($variableType->getTypes() as $variableTypeType) {
                if ($variableTypeType->isSuperTypeOf(new ObjectType('Argtyper202511\PHPUnit\Framework\MockObject\MockObject'))->yes()) {
                    return \true;
                }
            }
        }
        return \false;
    }
    private function isMockeryStaticCall(Node $node): bool
    {
        if (!$node instanceof StaticCall) {
            return \false;
        }
        // is mockery mock
        if (!$this->nodeNameResolver->isName($node->class, 'Mockery')) {
            return \false;
        }
        return $this->nodeNameResolver->isName($node->name, 'mock');
    }
}
