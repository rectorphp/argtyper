<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\Contract;

use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
interface NodeTypeResolverAwareInterface
{
    public function autowire(NodeTypeResolver $nodeTypeResolver): void;
}
