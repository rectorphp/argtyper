<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\Contract;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Type\Type;
/**
 * @template TNode as \PhpParser\Node
 */
interface NodeTypeResolverInterface
{
    /**
     * @return array<class-string<TNode>>
     */
    public function getNodeClasses() : array;
    /**
     * @param TNode $node
     */
    public function resolve(Node $node) : Type;
}
