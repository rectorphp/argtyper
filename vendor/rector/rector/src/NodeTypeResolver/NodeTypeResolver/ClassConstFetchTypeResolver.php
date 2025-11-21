<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @implements NodeTypeResolverInterface<ClassConstFetch>
 */
final class ClassConstFetchTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function resolve(Node $node) : Type
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        if ($node->class instanceof FullyQualified) {
            return $scope->getType($node);
        }
        if ($node->class instanceof Name && $node->class->hasAttribute(AttributeKey::NAMESPACED_NAME)) {
            $newNode = clone $node;
            $newNode->class = new FullyQualified($node->class->getAttribute(AttributeKey::NAMESPACED_NAME));
            return $scope->getType($newNode);
        }
        return $scope->getType($node);
    }
}
