<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\NodeFinder;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class ArrayDimFetchFinder
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
     * @return Expr[]
     */
    public function findDimFetchAssignToVariableName(ClassMethod $classMethod, string $variableName) : array
    {
        $assigns = $this->betterNodeFinder->findInstancesOfScoped((array) $classMethod->stmts, Assign::class);
        $exprs = [];
        foreach ($assigns as $assign) {
            if (!$assign->var instanceof ArrayDimFetch) {
                continue;
            }
            $arrayDimFetch = $assign->var;
            if (!$arrayDimFetch->var instanceof Variable) {
                continue;
            }
            if (!$this->nodeNameResolver->isName($arrayDimFetch->var, $variableName)) {
                continue;
            }
            $exprs[] = $assign->expr;
        }
        return $exprs;
    }
    /**
     * @return ArrayDimFetch[]
     */
    public function findByVariableName(Node $node, string $variableName) : array
    {
        $dimFetches = $this->betterNodeFinder->findInstancesOfScoped([$node], ArrayDimFetch::class);
        return \array_filter($dimFetches, function (ArrayDimFetch $arrayDimFetch) use($variableName) : bool {
            if (!$arrayDimFetch->var instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($arrayDimFetch->var, $variableName);
        });
    }
    /**
     * @return ArrayDimFetch[]
     */
    public function findByDimName(ClassMethod $classMethod, string $dimName) : array
    {
        $dimFetches = $this->betterNodeFinder->findInstancesOfScoped([$classMethod], ArrayDimFetch::class);
        return \array_filter($dimFetches, function (ArrayDimFetch $arrayDimFetch) use($dimName) : bool {
            if (!$arrayDimFetch->dim instanceof Variable) {
                return \false;
            }
            return $this->nodeNameResolver->isName($arrayDimFetch->dim, $dimName);
        });
    }
}
