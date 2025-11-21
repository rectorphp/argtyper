<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\NodeFactory;

use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
use Argtyper202511\Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
final class AttrGroupsFactory
{
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    public function __construct(PhpAttributeGroupFactory $phpAttributeGroupFactory)
    {
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
    }
    /**
     * @param DoctrineTagAndAnnotationToAttribute[] $doctrineTagAndAnnotationToAttributes
     * @param Use_[] $uses
     * @return AttributeGroup[]
     */
    public function create(array $doctrineTagAndAnnotationToAttributes, array $uses): array
    {
        $attributeGroups = [];
        foreach ($doctrineTagAndAnnotationToAttributes as $doctrineTagAndAnnotationToAttribute) {
            $doctrineAnnotationTagValueNode = $doctrineTagAndAnnotationToAttribute->getDoctrineAnnotationTagValueNode();
            // add attributes
            $attributeGroups[] = $this->phpAttributeGroupFactory->create($doctrineAnnotationTagValueNode, $doctrineTagAndAnnotationToAttribute->getAnnotationToAttribute(), $uses);
        }
        return $attributeGroups;
    }
}
