<?php

declare (strict_types=1);
namespace Argtyper202511\PhpParser;

use Argtyper202511\PhpParser\NodeVisitor\FindingVisitor;
use Argtyper202511\PhpParser\NodeVisitor\FirstFindingVisitor;
class NodeFinder
{
    /**
     * Find all nodes satisfying a filter callback.
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param callable $filter Filter callback: function(Node $node) : bool
     *
     * @return Node[] Found nodes satisfying the filter callback
     */
    public function find($nodes, callable $filter) : array
    {
        if ($nodes === []) {
            return [];
        }
        if (!\is_array($nodes)) {
            $nodes = [$nodes];
        }
        $visitor = new FindingVisitor($filter);
        $traverser = new \Argtyper202511\PhpParser\NodeTraverser($visitor);
        $traverser->traverse($nodes);
        return $visitor->getFoundNodes();
    }
    /**
     * Find all nodes that are instances of a certain class.
     * @template TNode as Node
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param class-string<TNode> $class Class name
     *
     * @return TNode[] Found nodes (all instances of $class)
     */
    public function findInstanceOf($nodes, string $class) : array
    {
        return $this->find($nodes, function ($node) use($class) {
            return $node instanceof $class;
        });
    }
    /**
     * Find first node satisfying a filter callback.
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param callable $filter Filter callback: function(Node $node) : bool
     *
     * @return null|Node Found node (or null if none found)
     */
    public function findFirst($nodes, callable $filter) : ?\Argtyper202511\PhpParser\Node
    {
        if ($nodes === []) {
            return null;
        }
        if (!\is_array($nodes)) {
            $nodes = [$nodes];
        }
        $visitor = new FirstFindingVisitor($filter);
        $traverser = new \Argtyper202511\PhpParser\NodeTraverser($visitor);
        $traverser->traverse($nodes);
        return $visitor->getFoundNode();
    }
    /**
     * Find first node that is an instance of a certain class.
     *
     * @template TNode as Node
     *
     * @param Node|Node[] $nodes Single node or array of nodes to search in
     * @param class-string<TNode> $class Class name
     *
     * @return null|TNode Found node, which is an instance of $class (or null if none found)
     */
    public function findFirstInstanceOf($nodes, string $class) : ?\Argtyper202511\PhpParser\Node
    {
        return $this->findFirst($nodes, function ($node) use($class) {
            return $node instanceof $class;
        });
    }
}
