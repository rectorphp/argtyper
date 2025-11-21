<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverAwareInterface;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\ParamTypeResolver\ParamTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Param>
 */
final class ParamTypeResolver implements NodeTypeResolverInterface, NodeTypeResolverAwareInterface
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function autowire(NodeTypeResolver $nodeTypeResolver) : void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Param::class];
    }
    /**
     * @param Param $node
     */
    public function resolve(Node $node) : Type
    {
        if ($node->type === null) {
            return new MixedType();
        }
        return $this->nodeTypeResolver->getType($node->type);
    }
}
