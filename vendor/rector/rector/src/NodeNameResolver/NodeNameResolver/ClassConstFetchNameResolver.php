<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\Rector\NodeNameResolver\Contract\NodeNameResolverInterface;
/**
 * @implements NodeNameResolverInterface<ClassConstFetch>
 */
final class ClassConstFetchNameResolver implements NodeNameResolverInterface
{
    public function getNode(): string
    {
        return ClassConstFetch::class;
    }
    /**
     * @param ClassConstFetch $node
     */
    public function resolve(Node $node, ?Scope $scope): ?string
    {
        if ($node->class instanceof Expr) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        $class = $node->class->toString();
        $name = $node->name->toString();
        return $class . '::' . $name;
    }
}
