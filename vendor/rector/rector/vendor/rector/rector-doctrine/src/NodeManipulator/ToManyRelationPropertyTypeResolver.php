<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeManipulator;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\CollectionMapping;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\DocumentMappingKey;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Argtyper202511\Rector\Doctrine\Enum\DoctrineClass;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Argtyper202511\Rector\Doctrine\PhpDoc\ShortClassExpander;
use Argtyper202511\Rector\Doctrine\TypeAnalyzer\CollectionTypeFactory;
use Argtyper202511\Rector\Doctrine\TypeAnalyzer\CollectionTypeResolver;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class ToManyRelationPropertyTypeResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\PhpDoc\ShortClassExpander
     */
    private $shortClassExpander;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
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
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ShortClassExpander $shortClassExpander, AttributeFinder $attributeFinder, ValueResolver $valueResolver, CollectionTypeFactory $collectionTypeFactory, CollectionTypeResolver $collectionTypeResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->shortClassExpander = $shortClassExpander;
        $this->attributeFinder = $attributeFinder;
        $this->valueResolver = $valueResolver;
        $this->collectionTypeFactory = $collectionTypeFactory;
        $this->collectionTypeResolver = $collectionTypeResolver;
    }
    public function resolve(Property $property) : ?Type
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClasses(CollectionMapping::TO_MANY_CLASSES);
        if ($doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return $this->processToManyRelation($property, $doctrineAnnotationTagValueNode);
        }
        $expr = $this->attributeFinder->findAttributeByClassesArgByNames($property, CollectionMapping::TO_MANY_CLASSES, [EntityMappingKey::TARGET_ENTITY, DocumentMappingKey::TARGET_DOCUMENT]);
        if (!$expr instanceof Expr) {
            return null;
        }
        return $this->resolveTypeFromTargetEntity($expr, $property);
    }
    private function processToManyRelation(Property $property, DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : ?\Argtyper202511\PHPStan\Type\Type
    {
        $targetEntityArrayItemNode = $doctrineAnnotationTagValueNode->getValue(EntityMappingKey::TARGET_ENTITY) ?: $doctrineAnnotationTagValueNode->getValue(DocumentMappingKey::TARGET_DOCUMENT);
        if (!$targetEntityArrayItemNode instanceof ArrayItemNode) {
            // most likely mapped superclass
            return new ObjectType(DoctrineClass::COLLECTION);
        }
        $targetEntityClass = $targetEntityArrayItemNode->value;
        if ($targetEntityClass instanceof StringNode) {
            $targetEntityClass = $targetEntityClass->value;
        }
        if (!\is_string($targetEntityClass)) {
            return null;
        }
        return $this->resolveTypeFromTargetEntity($targetEntityClass, $property);
    }
    /**
     * @param \PhpParser\Node\Expr|string $targetEntity
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    private function resolveTypeFromTargetEntity($targetEntity, $property) : Type
    {
        if ($targetEntity instanceof Expr) {
            $targetEntity = $this->valueResolver->getValue($targetEntity);
        }
        if (!\is_string($targetEntity)) {
            return new FullyQualifiedObjectType(DoctrineClass::COLLECTION);
        }
        $entityFullyQualifiedClass = $this->shortClassExpander->resolveFqnTargetEntity($targetEntity, $property);
        $fullyQualifiedObjectType = new FullyQualifiedObjectType($entityFullyQualifiedClass);
        return $this->collectionTypeFactory->createType($fullyQualifiedObjectType, $this->collectionTypeResolver->hasIndexBy($property), $property);
    }
}
