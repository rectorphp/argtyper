<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\NodeAnalyser;

use Argtyper202511\PhpParser\Node\ClosureUse;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class ClosureUsesResolver
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
     * @return ClosureUse[]
     */
    public function resolveFromArrowFunction(ArrowFunction $arrowFunction): array
    {
        // fill needed uses from arrow function to closure
        $arrowFunctionVariables = $this->betterNodeFinder->findInstancesOfScoped($arrowFunction->getStmts(), Variable::class);
        $paramNames = $this->resolveParamNames($arrowFunction);
        $externalVariableNames = [];
        foreach ($arrowFunctionVariables as $arrowFunctionVariable) {
            // skip those defined in params
            if ($this->nodeNameResolver->isNames($arrowFunctionVariable, $paramNames)) {
                continue;
            }
            $variableName = $this->nodeNameResolver->getName($arrowFunctionVariable);
            if (!is_string($variableName)) {
                continue;
            }
            $externalVariableNames[] = $variableName;
        }
        $externalVariableNames = array_unique($externalVariableNames);
        $externalVariableNames = array_diff($externalVariableNames, ['this']);
        return $this->createClosureUses($externalVariableNames);
    }
    /**
     * @return string[]
     */
    private function resolveParamNames(ArrowFunction $arrowFunction): array
    {
        $paramNames = [];
        foreach ($arrowFunction->getParams() as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }
        return $paramNames;
    }
    /**
     * @param string[] $externalVariableNames
     * @return ClosureUse[]
     */
    private function createClosureUses(array $externalVariableNames): array
    {
        $closureUses = [];
        foreach ($externalVariableNames as $externalVariableName) {
            $closureUses[] = new ClosureUse(new Variable($externalVariableName));
        }
        return $closureUses;
    }
}
