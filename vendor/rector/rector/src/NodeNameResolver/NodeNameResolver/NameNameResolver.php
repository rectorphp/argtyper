<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Analyser\Scope;
use Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Name>
 */
final class NameNameResolver implements NodeNameResolverInterface
{
    public function getNode(): string
    {
        return Name::class;
    }
    /**
     * @param Name $node
     */
    public function resolve(Node $node, ?Scope $scope): string
    {
        return $node->toString();
    }
}
