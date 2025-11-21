<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Error;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Param>
 */
final class ParamNameResolver implements NodeNameResolverInterface
{
    public function getNode(): string
    {
        return Param::class;
    }
    /**
     * @param Param $node
     */
    public function resolve(Node $node, ?Scope $scope): ?string
    {
        if ($node->var instanceof Error) {
            return null;
        }
        if ($node->var->name instanceof Expr) {
            return null;
        }
        return $node->var->name;
    }
}
