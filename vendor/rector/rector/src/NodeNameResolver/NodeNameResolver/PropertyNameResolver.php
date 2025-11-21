<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<Property>
 */
final class PropertyNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return Property::class;
    }
    /**
     * @param Property $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->props === []) {
            return null;
        }
        $onlyProperty = $node->props[0];
        return $onlyProperty->name->toString();
    }
}
