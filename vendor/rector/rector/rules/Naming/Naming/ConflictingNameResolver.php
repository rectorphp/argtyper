<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\Naming;

use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver;
use Argtyper202511\Rector\Naming\PhpArray\ArrayFilter;
use Argtyper202511\Rector\NodeManipulator\FunctionLikeManipulator;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class ConflictingNameResolver
{
    /**
     * @readonly
     * @var \Rector\Naming\PhpArray\ArrayFilter
     */
    private $arrayFilter;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\ExpectedNameResolver
     */
    private $expectedNameResolver;
    /**
     * @readonly
     * @var \Rector\Naming\ExpectedNameResolver\MatchParamTypeExpectedNameResolver
     */
    private $matchParamTypeExpectedNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\FunctionLikeManipulator
     */
    private $functionLikeManipulator;
    /**
     * @var array<int, string[]>
     */
    private $conflictingVariableNamesByClassMethod = [];
    public function __construct(ArrayFilter $arrayFilter, BetterNodeFinder $betterNodeFinder, \Argtyper202511\Rector\Naming\Naming\ExpectedNameResolver $expectedNameResolver, MatchParamTypeExpectedNameResolver $matchParamTypeExpectedNameResolver, FunctionLikeManipulator $functionLikeManipulator)
    {
        $this->arrayFilter = $arrayFilter;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->expectedNameResolver = $expectedNameResolver;
        $this->matchParamTypeExpectedNameResolver = $matchParamTypeExpectedNameResolver;
        $this->functionLikeManipulator = $functionLikeManipulator;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $classMethod
     */
    public function resolveConflictingVariableNamesForParam($classMethod) : array
    {
        $expectedNames = [];
        foreach ($classMethod->params as $param) {
            $expectedName = $this->matchParamTypeExpectedNameResolver->resolve($param);
            if ($expectedName === null) {
                continue;
            }
            $expectedNames[] = $expectedName;
        }
        return $this->arrayFilter->filterWithAtLeastTwoOccurrences($expectedNames);
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function hasNameIsInFunctionLike(string $variableName, $functionLike) : bool
    {
        $conflictingVariableNames = $this->resolveConflictingVariableNamesForNew($functionLike);
        return \in_array($variableName, $conflictingVariableNames, \true);
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function resolveConflictingVariableNamesForNew($functionLike) : array
    {
        // cache it!
        $classMethodId = \spl_object_id($functionLike);
        if (isset($this->conflictingVariableNamesByClassMethod[$classMethodId])) {
            return $this->conflictingVariableNamesByClassMethod[$classMethodId];
        }
        $paramNames = $this->functionLikeManipulator->resolveParamNames($functionLike);
        $newAssignNames = $this->resolveForNewAssigns($functionLike);
        $nonNewAssignNames = $this->resolveForNonNewAssigns($functionLike);
        $protectedNames = \array_merge($paramNames, $newAssignNames, $nonNewAssignNames);
        $protectedNames = $this->arrayFilter->filterWithAtLeastTwoOccurrences($protectedNames);
        $this->conflictingVariableNamesByClassMethod[$classMethodId] = $protectedNames;
        return $protectedNames;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function resolveForNewAssigns($functionLike) : array
    {
        $names = [];
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->getStmts(), Assign::class);
        foreach ($assigns as $assign) {
            $name = $this->expectedNameResolver->resolveForAssignNew($assign);
            if ($name === null) {
                continue;
            }
            $names[] = $name;
        }
        return $names;
    }
    /**
     * @return string[]
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    private function resolveForNonNewAssigns($functionLike) : array
    {
        $names = [];
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $functionLike->getStmts(), Assign::class);
        foreach ($assigns as $assign) {
            $name = $this->expectedNameResolver->resolveForAssignNonNew($assign);
            if ($name === null) {
                continue;
            }
            $names[] = $name;
        }
        return $names;
    }
}
