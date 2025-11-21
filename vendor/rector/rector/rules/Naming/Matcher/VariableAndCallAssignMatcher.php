<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Matcher;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\Rector\Naming\ValueObject\VariableAndCallAssign;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class VariableAndCallAssignMatcher
{
    /**
     * @readonly
     * @var \Rector\Naming\Matcher\CallMatcher
     */
    private $callMatcher;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(\Argtyper202511\Rector\Naming\Matcher\CallMatcher $callMatcher, NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->callMatcher = $callMatcher;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Stmt\Function_ $functionLike
     */
    public function match(Assign $assign, $functionLike) : ?VariableAndCallAssign
    {
        $call = $this->callMatcher->matchCall($assign);
        if (!$call instanceof Node) {
            return null;
        }
        if (!$assign->var instanceof Variable) {
            return null;
        }
        $variableName = $this->nodeNameResolver->getName($assign->var);
        if ($variableName === null) {
            return null;
        }
        $isVariableFoundInCallArgs = (bool) $this->betterNodeFinder->findFirst($call->isFirstClassCallable() ? [] : $call->getArgs(), function (Node $subNode) use($variableName) : bool {
            return $subNode instanceof Variable && $this->nodeNameResolver->isName($subNode, $variableName);
        });
        if ($isVariableFoundInCallArgs) {
            return null;
        }
        return new VariableAndCallAssign($assign->var, $call, $assign, $variableName, $functionLike);
    }
}
