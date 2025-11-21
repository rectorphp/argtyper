<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<ClassLike>
 */
final class ClassNameResolver implements NodeNameResolverInterface
{
    public function getNode() : string
    {
        return ClassLike::class;
    }
    /**
     * @param ClassLike $node
     */
    public function resolve(Node $node, ?Scope $scope) : ?string
    {
        if ($node->namespacedName instanceof Name) {
            return $node->namespacedName->toString();
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        return $node->name->toString();
    }
}
