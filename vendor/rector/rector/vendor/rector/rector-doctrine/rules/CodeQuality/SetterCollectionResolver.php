<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\PHPStan\Reflection\Php\PhpPropertyReflection;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Argtyper202511\Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Argtyper202511\Rector\Doctrine\TypeAnalyzer\CollectionVarTagValueNodeResolver;
use Argtyper202511\Rector\NodeManipulator\AssignManipulator;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class SetterCollectionResolver
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\AssignManipulator
     */
    private $assignManipulator;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionVarTagValueNodeResolver
     */
    private $collectionVarTagValueNodeResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory
     */
    private $collectionTypeFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver
     */
    private $collectionTypeResolver;
    public function __construct(AssignManipulator $assignManipulator, ReflectionResolver $reflectionResolver, NodeNameResolver $nodeNameResolver, CollectionVarTagValueNodeResolver $collectionVarTagValueNodeResolver, StaticTypeMapper $staticTypeMapper, CollectionTypeFactory $collectionTypeFactory, CollectionTypeResolver $collectionTypeResolver)
    {
        $this->assignManipulator = $assignManipulator;
        $this->reflectionResolver = $reflectionResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->collectionVarTagValueNodeResolver = $collectionVarTagValueNodeResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->collectionTypeResolver = $collectionTypeResolver;
    }
    public function resolveAssignedGenericCollectionType(Class_ $class, ClassMethod $classMethod) : ?GenericObjectType
    {
        $propertyFetches = $this->assignManipulator->resolveAssignsToLocalPropertyFetches($classMethod);
        if (\count($propertyFetches) !== 1) {
            return null;
        }
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetches[0]);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return null;
        }
        $propertyName = (string) $this->nodeNameResolver->getName($propertyFetches[0]);
        $property = $class->getProperty($propertyName);
        if (!$property instanceof Property) {
            return null;
        }
        $varTagValueNode = $this->collectionVarTagValueNodeResolver->resolve($property);
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return null;
        }
        // remove collection union type, so this can be turned into generic type
        $resolvedType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
        if ($resolvedType instanceof UnionType) {
            $nonCollectionTypes = [];
            foreach ($resolvedType->getTypes() as $unionedType) {
                if (!$this->isCollectionType($unionedType)) {
                    continue;
                }
                $nonCollectionTypes[] = $unionedType;
            }
            if (\count($nonCollectionTypes) === 1) {
                $soleType = $nonCollectionTypes[0];
                if ($soleType instanceof ArrayType && $soleType->getItemType() instanceof ObjectType) {
                    return $this->collectionTypeFactory->createType($soleType->getItemType(), $this->collectionTypeResolver->hasIndexBy($property), $property);
                }
            }
        }
        if ($resolvedType instanceof GenericObjectType) {
            return $resolvedType;
        }
        return null;
    }
    private function isCollectionType(Type $type) : bool
    {
        if ($type instanceof ShortenedObjectType && $type->getFullyQualifiedName() === DoctrineClass::COLLECTION) {
            return \true;
        }
        return $type instanceof ObjectType && $type->getClassName() === DoctrineClass::COLLECTION;
    }
}
