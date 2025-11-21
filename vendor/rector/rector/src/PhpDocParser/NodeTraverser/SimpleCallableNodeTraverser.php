<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpDocParser\NodeTraverser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\NodeTraverser;
use Argtyper202511\Rector\PhpDocParser\NodeVisitor\CallableNodeVisitor;
/**
 * @api
 */
final class SimpleCallableNodeTraverser
{
    /**
     * @param callable(Node): (int|Node|null|Node[]) $callable
     * @param Node|Node[]|null $node
     */
    public function traverseNodesWithCallable($node, callable $callable) : void
    {
        if ($node === null || $node === []) {
            return;
        }
        $callableNodeVisitor = new CallableNodeVisitor($callable);
        $nodeTraverser = new NodeTraverser($callableNodeVisitor);
        $nodes = $node instanceof Node ? [$node] : $node;
        $nodeTraverser->traverse($nodes);
    }
}
