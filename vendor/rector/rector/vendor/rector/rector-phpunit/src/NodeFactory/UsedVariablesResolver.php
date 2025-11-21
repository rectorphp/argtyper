<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\NodeFactory;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\PHPUnit\Enum\ConsecutiveVariable;
final class UsedVariablesResolver
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
     * @return Variable[]
     */
    public function resolveUsedVariables(MethodCall $withConsecutiveMethodCall, ?Stmt $returnStmt): array
    {
        $consecutiveArgs = $withConsecutiveMethodCall->getArgs();
        $stmtVariables = $returnStmt instanceof Stmt ? $this->resolveUniqueVariables([$returnStmt]) : [];
        return $this->resolveUniqueVariables(array_merge($consecutiveArgs, $stmtVariables));
    }
    /**
     * @param Node[] $nodes
     * @return Variable[]
     */
    private function resolveUniqueVariables(array $nodes): array
    {
        /** @var Variable[] $usedVariables */
        $usedVariables = $this->betterNodeFinder->findInstancesOfScoped($nodes, Variable::class);
        $uniqueUsedVariables = [];
        foreach ($usedVariables as $usedVariable) {
            if ($this->nodeNameResolver->isNames($usedVariable, ['this', ConsecutiveVariable::MATCHER, ConsecutiveVariable::PARAMETERS])) {
                continue;
            }
            $usedVariableName = $this->nodeNameResolver->getName($usedVariable);
            $uniqueUsedVariables[$usedVariableName] = $usedVariable;
        }
        return $uniqueUsedVariables;
    }
}
