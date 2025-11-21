<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php84\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
final class ForeachKeyUsedInConditionalAnalyzer
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
    public function isUsed(Variable $variable, Expr $expr) : bool
    {
        $keyVarName = (string) $this->nodeNameResolver->getName($variable);
        return (bool) $this->betterNodeFinder->findVariableOfName($expr, $keyVarName);
    }
}
