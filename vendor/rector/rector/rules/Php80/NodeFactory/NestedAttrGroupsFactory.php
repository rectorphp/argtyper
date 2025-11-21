<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\NodeFactory;

use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\Rector\Php80\ValueObject\NestedDoctrineTagAndAnnotationToAttribute;
use Argtyper202511\Rector\PhpAttribute\NodeFactory\PhpNestedAttributeGroupFactory;
final class NestedAttrGroupsFactory
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpNestedAttributeGroupFactory
     */
    private $phpNestedAttributeGroupFactory;
    public function __construct(PhpNestedAttributeGroupFactory $phpNestedAttributeGroupFactory)
    {
        $this->phpNestedAttributeGroupFactory = $phpNestedAttributeGroupFactory;
    }
    /**
     * @param NestedDoctrineTagAndAnnotationToAttribute[] $nestedDoctrineTagAndAnnotationToAttributes
     * @param Use_[] $uses
     * @return AttributeGroup[]
     */
    public function create(array $nestedDoctrineTagAndAnnotationToAttributes, array $uses) : array
    {
        $attributeGroups = [];
        foreach ($nestedDoctrineTagAndAnnotationToAttributes as $nestedDoctrineTagAndAnnotationToAttribute) {
            $doctrineAnnotationTagValueNode = $nestedDoctrineTagAndAnnotationToAttribute->getDoctrineAnnotationTagValueNode();
            $nestedAnnotationToAttribute = $nestedDoctrineTagAndAnnotationToAttribute->getNestedAnnotationToAttribute();
            // do not create alternative for the annotation, only unwrap
            if (!$nestedAnnotationToAttribute->shouldRemoveOriginal()) {
                // add attributes
                $attributeGroups[] = $this->phpNestedAttributeGroupFactory->create($doctrineAnnotationTagValueNode, $nestedDoctrineTagAndAnnotationToAttribute->getNestedAnnotationToAttribute(), $uses);
            }
            $nestedAttributeGroups = $this->phpNestedAttributeGroupFactory->createNested($doctrineAnnotationTagValueNode, $nestedDoctrineTagAndAnnotationToAttribute->getNestedAnnotationToAttribute());
            $attributeGroups = \array_merge($attributeGroups, $nestedAttributeGroups);
        }
        return \array_unique($attributeGroups, \SORT_REGULAR);
    }
}
