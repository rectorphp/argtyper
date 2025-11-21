<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\NodeVisitor;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PhpParser\NodeVisitorAbstract;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Scope\Contract\NodeVisitor\ScopeResolverNodeVisitorInterface;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class ByRefReturnNodeVisitor extends NodeVisitorAbstract implements ScopeResolverNodeVisitorInterface
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
    }
    public function enterNode(Node $node) : ?Node
    {
        if (!$node instanceof FunctionLike) {
            return null;
        }
        if (!$node->returnsByRef()) {
            return null;
        }
        $stmts = $node->getStmts();
        if ($stmts === null) {
            return null;
        }
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable($stmts, static function (Node $node) {
            if ($node instanceof Class_ || $node instanceof FunctionLike) {
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof Return_) {
                return null;
            }
            $node->setAttribute(AttributeKey::IS_BYREF_RETURN, \true);
            return $node;
        });
        return null;
    }
}
