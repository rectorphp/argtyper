<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\NodeFinder;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class MethodCallNodeFinder
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
    /**
     * @param string[] $methodNames
     */
    public function hasByNames(Expression $expression, array $methodNames): bool
    {
        $desiredMethodCalls = $this->betterNodeFinder->find($expression, function (Node $node) use ($methodNames): bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->nodeNameResolver->isNames($node->name, $methodNames);
        });
        return $desiredMethodCalls !== [];
    }
    public function findByName(Expression $expression, string $methodName): ?MethodCall
    {
        if (!$expression->expr instanceof MethodCall) {
            return null;
        }
        /** @var MethodCall|null $methodCall */
        $methodCall = $this->betterNodeFinder->findFirst($expression->expr, function (Node $node) use ($methodName): bool {
            if (!$node instanceof MethodCall) {
                return \false;
            }
            return $this->nodeNameResolver->isName($node->name, $methodName);
        });
        return $methodCall;
    }
}
