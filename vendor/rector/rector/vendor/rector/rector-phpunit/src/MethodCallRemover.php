<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Stmt\Expression;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class MethodCallRemover
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
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function removeMethodCall(Expression $expression, string $methodName) : void
    {
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($expression, function (Node $node) use($methodName) : ?Node {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node->name, $methodName)) {
                return null;
            }
            return $node->var;
        });
    }
}
