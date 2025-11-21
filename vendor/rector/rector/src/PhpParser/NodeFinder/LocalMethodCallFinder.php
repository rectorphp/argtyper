<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpParser\NodeFinder;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class LocalMethodCallFinder
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return MethodCall[]|StaticCall[]
     */
    public function match(Class_ $class, ClassMethod $classMethod): array
    {
        $className = $this->nodeNameResolver->getName($class);
        if (!is_string($className)) {
            return [];
        }
        $classMethodName = $this->nodeNameResolver->getName($classMethod);
        /** @var MethodCall[]|StaticCall[] $matchingMethodCalls */
        $matchingMethodCalls = $this->betterNodeFinder->find($class->getMethods(), function (Node $subNode) use ($className, $classMethodName): bool {
            if (!$subNode instanceof MethodCall && !$subNode instanceof StaticCall) {
                return \false;
            }
            if (!$this->nodeNameResolver->isName($subNode->name, $classMethodName)) {
                return \false;
            }
            $callerType = $subNode instanceof MethodCall ? $this->nodeTypeResolver->getType($subNode->var) : $this->nodeTypeResolver->getType($subNode->class);
            return ClassNameFromObjectTypeResolver::resolve($callerType) === $className;
        });
        return $matchingMethodCalls;
    }
}
