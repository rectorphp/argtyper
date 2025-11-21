<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
use Argtyper202511\Rector\Php81\Enum\AttributeName;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AttributeArrayNameInliner;
/**
 * @see \Rector\Tests\PhpAttribute\Printer\PhpAttributeGroupFactoryTest
 */
final class PhpAttributeGroupFactory
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\AttributeNameFactory
     */
    private $attributeNameFactory;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\NamedArgsFactory
     */
    private $namedArgsFactory;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\AnnotationToAttributeIntegerValueCaster
     */
    private $annotationToAttributeIntegerValueCaster;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\AttributeArrayNameInliner
     */
    private $attributeArrayNameInliner;
    public function __construct(AnnotationToAttributeMapper $annotationToAttributeMapper, \Argtyper202511\Rector\PhpAttribute\NodeFactory\AttributeNameFactory $attributeNameFactory, \Argtyper202511\Rector\PhpAttribute\NodeFactory\NamedArgsFactory $namedArgsFactory, \Argtyper202511\Rector\PhpAttribute\NodeFactory\AnnotationToAttributeIntegerValueCaster $annotationToAttributeIntegerValueCaster, AttributeArrayNameInliner $attributeArrayNameInliner)
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
        $this->attributeNameFactory = $attributeNameFactory;
        $this->namedArgsFactory = $namedArgsFactory;
        $this->annotationToAttributeIntegerValueCaster = $annotationToAttributeIntegerValueCaster;
        $this->attributeArrayNameInliner = $attributeArrayNameInliner;
    }
    public function createFromSimpleTag(AnnotationToAttribute $annotationToAttribute, ?string $value = null): AttributeGroup
    {
        return $this->createFromClass($annotationToAttribute->getAttributeClass(), $value);
    }
    /**
     * @param AttributeName::*|string $attributeClass
     */
    public function createFromClass(string $attributeClass, ?string $value = null): AttributeGroup
    {
        $fullyQualified = new FullyQualified($attributeClass);
        $attribute = new Attribute($fullyQualified);
        if ($value !== null && $value !== '') {
            $arg = new Arg(new String_($value));
            $attribute->args = [$arg];
        }
        return new AttributeGroup([$attribute]);
    }
    /**
     * @api tests
     * @param mixed[] $items
     */
    public function createFromClassWithItems(string $attributeClass, array $items): AttributeGroup
    {
        $fullyQualified = new FullyQualified($attributeClass);
        $args = $this->createArgsFromItems($items);
        $attribute = new Attribute($fullyQualified, $args);
        return new AttributeGroup([$attribute]);
    }
    /**
     * @param Use_[] $uses
     */
    public function create(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, AnnotationToAttribute $annotationToAttribute, array $uses): AttributeGroup
    {
        $values = $doctrineAnnotationTagValueNode->getValuesWithSilentKey();
        $args = $this->createArgsFromItems($values, '', $annotationToAttribute->getClassReferenceFields());
        $this->annotationToAttributeIntegerValueCaster->castAttributeTypes($annotationToAttribute, $args);
        $args = $this->attributeArrayNameInliner->inlineArrayToArgs($args);
        $attributeName = $this->attributeNameFactory->create($annotationToAttribute, $doctrineAnnotationTagValueNode, $uses);
        // keep FQN in the attribute, so it can be easily detected later
        $attributeName->setAttribute(AttributeKey::PHP_ATTRIBUTE_NAME, $annotationToAttribute->getAttributeClass());
        $attribute = new Attribute($attributeName, $args);
        return new AttributeGroup([$attribute]);
    }
    /**
     * @api tests
     *
     * @param ArrayItemNode[]|mixed[] $items
     * @param string $attributeClass @deprecated
     * @param string[] $classReferencedFields
     *
     * @return list<Arg>
     */
    public function createArgsFromItems(array $items, string $attributeClass = '', array $classReferencedFields = []): array
    {
        $mappedItems = $this->annotationToAttributeMapper->map($items);
        $this->mapClassReferences($mappedItems, $classReferencedFields);
        $values = $mappedItems instanceof Array_ ? $mappedItems->items : $mappedItems;
        // the key here should contain the named argument
        return $this->namedArgsFactory->createFromValues($values);
    }
    /**
     * @param string[] $classReferencedFields
     * @param \PhpParser\Node\Expr|string $expr
     */
    private function mapClassReferences($expr, array $classReferencedFields): void
    {
        if (!$expr instanceof Array_) {
            return;
        }
        foreach ($expr->items as $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if (!in_array($arrayItem->key->value, $classReferencedFields)) {
                continue;
            }
            if ($arrayItem->value instanceof ClassConstFetch) {
                continue;
            }
            if (!$arrayItem->value instanceof String_) {
                continue;
            }
            $arrayItem->value = new ClassConstFetch(new FullyQualified($arrayItem->value->value), 'class');
        }
    }
}
