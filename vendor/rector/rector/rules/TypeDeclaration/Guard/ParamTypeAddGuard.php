<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Guard;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\FunctionLike;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\If_;
use PhpParser\NodeVisitor;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Rector\PhpParser\Node\BetterNodeFinder;
final class ParamTypeAddGuard
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function isLegal(Param $param, FunctionLike $functionLike): bool
    {
        $paramName = $this->nodeNameResolver->getName($param);
        $isLegal = \true;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), function (Node $subNode) use (&$isLegal, $paramName): ?int {
            if ($subNode instanceof Assign && $subNode->var instanceof Variable && $this->nodeNameResolver->isName($subNode->var, $paramName)) {
                $isLegal = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if ($subNode instanceof If_ && (bool) $this->betterNodeFinder->findFirst($subNode->cond, function (Node $node) use ($paramName): bool {
                return $node instanceof Variable && $this->nodeNameResolver->isName($node, $paramName);
            })) {
                $isLegal = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if ($subNode instanceof Ternary && (bool) $this->betterNodeFinder->findFirst($subNode, function (Node $node) use ($paramName): bool {
                return $node instanceof Variable && $this->nodeNameResolver->isName($node, $paramName);
            })) {
                $isLegal = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            return null;
        });
        return $isLegal;
    }
}
