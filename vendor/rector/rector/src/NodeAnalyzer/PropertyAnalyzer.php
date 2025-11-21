<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\NonExistingObjectType;
final class PropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function hasForbiddenType(Property $property): bool
    {
        $propertyType = $this->nodeTypeResolver->getType($property);
        if ($propertyType->isNull()->yes()) {
            return \true;
        }
        if ($this->isForbiddenType($propertyType)) {
            return \true;
        }
        if (!$propertyType instanceof UnionType) {
            return \false;
        }
        $types = $propertyType->getTypes();
        foreach ($types as $type) {
            if ($this->isForbiddenType($type)) {
                return \true;
            }
        }
        return \false;
    }
    public function isForbiddenType(Type $type): bool
    {
        if ($type instanceof NonExistingObjectType) {
            return \true;
        }
        return $this->isCallableType($type);
    }
    private function isCallableType(Type $type): bool
    {
        if (ClassNameFromObjectTypeResolver::resolve($type) === 'Closure') {
            return \false;
        }
        return $type->isCallable()->yes();
    }
}
