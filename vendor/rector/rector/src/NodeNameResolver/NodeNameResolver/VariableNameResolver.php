<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Variable>
 */
final class VariableNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return Variable::class;
    }
    /**
     * @param Variable $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->name instanceof Expr) {
            return null;
        }
        return $node->name;
    }
}
