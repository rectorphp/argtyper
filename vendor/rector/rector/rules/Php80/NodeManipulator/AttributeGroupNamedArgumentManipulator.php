<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\NodeManipulator;

use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Php80\Contract\ConverterAttributeDecoratorInterface;
final class AttributeGroupNamedArgumentManipulator
{
    /**
     * @var ConverterAttributeDecoratorInterface[]
     * @readonly
     */
    private $converterAttributeDecorators;
    /**
     * @param ConverterAttributeDecoratorInterface[] $converterAttributeDecorators
     */
    public function __construct(array $converterAttributeDecorators)
    {
        $this->converterAttributeDecorators = $converterAttributeDecorators;
    }
    /**
     * @param AttributeGroup[] $attributeGroups
     */
    public function decorate(array $attributeGroups) : void
    {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attr) {
                $phpAttributeName = $attr->name->getAttribute(AttributeKey::PHP_ATTRIBUTE_NAME);
                foreach ($this->converterAttributeDecorators as $converterAttributeDecorator) {
                    if ($converterAttributeDecorator->getAttributeName() !== $phpAttributeName) {
                        continue;
                    }
                    $converterAttributeDecorator->decorate($attr);
                }
            }
        }
    }
}
