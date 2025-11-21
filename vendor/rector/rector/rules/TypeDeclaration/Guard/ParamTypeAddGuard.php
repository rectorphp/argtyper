<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Guard;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Ternary;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
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
    public function isLegal(Param $param, FunctionLike $functionLike) : bool
    {
        $paramName = $this->nodeNameResolver->getName($param);
        $isLegal = \true;
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $functionLike->getStmts(), function (Node $subNode) use(&$isLegal, $paramName) : ?int {
            if ($subNode instanceof Assign && $subNode->var instanceof Variable && $this->nodeNameResolver->isName($subNode->var, $paramName)) {
                $isLegal = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if ($subNode instanceof If_ && (bool) $this->betterNodeFinder->findFirst($subNode->cond, function (Node $node) use($paramName) : bool {
                return $node instanceof Variable && $this->nodeNameResolver->isName($node, $paramName);
            })) {
                $isLegal = \false;
                return NodeVisitor::STOP_TRAVERSAL;
            }
            if ($subNode instanceof Ternary && (bool) $this->betterNodeFinder->findFirst($subNode, function (Node $node) use($paramName) : bool {
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
