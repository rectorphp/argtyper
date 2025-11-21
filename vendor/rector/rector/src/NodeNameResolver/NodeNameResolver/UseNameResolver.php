<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Use_>
 */
final class UseNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return Use_::class;
    }
    /**
     * @param Use_ $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->uses === []) {
            return null;
        }
        $onlyUse = $node->uses[0];
        return $onlyUse->name->toString();
    }
}
