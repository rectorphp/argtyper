<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyTypeResolver\PropertyTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Property>
 */
final class PropertyTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver\PropertyFetchTypeResolver
     */
    private $propertyFetchTypeResolver;
    public function __construct(\Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver\PropertyFetchTypeResolver $propertyFetchTypeResolver)
    {
        $this->propertyFetchTypeResolver = $propertyFetchTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function resolve(Node $node): Type
    {
        // fake property to local PropertyFetch → PHPStan understands that
        $propertyFetch = new PropertyFetch(new Variable('this'), (string) $node->props[0]->name);
        $propertyFetch->setAttribute(AttributeKey::SCOPE, $node->getAttribute(AttributeKey::SCOPE));
        return $this->propertyFetchTypeResolver->resolve($propertyFetch);
    }
}
