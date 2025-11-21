<?php

declare (strict_types=1);
namespace Rector\NodeNameResolver\Contract;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Analyser\Scope;
/**
 * @template TNode as Node
 */
interface NodeNameResolverInterface
{
    /**
     * @return class-string<TNode>
     */
    public function getNode(): string;
    /**
     * @param TNode $node
     */
    public function resolve(Node $node, ?Scope $scope): ?string;
}
