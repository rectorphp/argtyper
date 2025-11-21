<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Enum\ObjectReference;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see \Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\NameTypeResolver\NameTypeResolverTest
 *
 * @implements NodeTypeResolverInterface<Name|FullyQualified>
 */
final class NameTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [Name::class, FullyQualified::class];
    }
    /**
     * @param Name $node
     */
    public function resolve(Node $node): Type
    {
        // not instanceof FullyQualified means it is a Name
        if (!$node instanceof FullyQualified && $node->hasAttribute(AttributeKey::NAMESPACED_NAME)) {
            return $this->resolve(new FullyQualified($node->getAttribute(AttributeKey::NAMESPACED_NAME)));
        }
        if ($node->toString() === ObjectReference::PARENT) {
            return $this->resolveParent($node);
        }
        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);
        return new ObjectType($fullyQualifiedName);
    }
    /**
     * @param \PhpParser\Node\Name|\PhpParser\Node\Name\FullyQualified $node
     */
    private function resolveClassReflection($node): ?ClassReflection
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        return $scope->getClassReflection();
    }
    /**
     * @return \PHPStan\Type\MixedType|\PHPStan\Type\ObjectType|\PHPStan\Type\UnionType
     */
    private function resolveParent(Name $name)
    {
        $classReflection = $this->resolveClassReflection($name);
        if (!$classReflection instanceof ClassReflection || !$classReflection->isClass()) {
            return new MixedType();
        }
        if ($classReflection->isAnonymous()) {
            return new MixedType();
        }
        $parentClassObjectTypes = [];
        foreach ($classReflection->getParents() as $parentClassReflection) {
            $parentClassObjectTypes[] = new ObjectType($parentClassReflection->getName());
        }
        if ($parentClassObjectTypes === []) {
            return new MixedType();
        }
        if (count($parentClassObjectTypes) === 1) {
            return $parentClassObjectTypes[0];
        }
        return new UnionType($parentClassObjectTypes);
    }
    private function resolveFullyQualifiedName(Name $name): string
    {
        $nameValue = $name->toString();
        if (in_array($nameValue, [ObjectReference::SELF, ObjectReference::STATIC], \true)) {
            $classReflection = $this->resolveClassReflection($name);
            if (!$classReflection instanceof ClassReflection || $classReflection->isAnonymous()) {
                return $name->toString();
            }
            return $classReflection->getName();
        }
        return $nameValue;
    }
}
