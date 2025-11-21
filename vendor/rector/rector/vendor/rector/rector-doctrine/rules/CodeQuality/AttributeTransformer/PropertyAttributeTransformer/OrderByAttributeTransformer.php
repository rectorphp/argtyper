<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class OrderByAttributeTransformer implements PropertyAttributeTransformerInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function transform(EntityMapping $entityMapping, $property) : bool
    {
        $oneToManyMapping = $entityMapping->matchOneToManyPropertyMapping($property);
        if (!\is_array($oneToManyMapping)) {
            return \false;
        }
        // we handle OrderBy here only
        if (!isset($oneToManyMapping[EntityMappingKey::ORDER_BY])) {
            return \false;
        }
        $orderBy = $oneToManyMapping[EntityMappingKey::ORDER_BY];
        $args = $this->nodeFactory->createArgs([$orderBy]);
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
        return \true;
    }
    public function getClassName() : string
    {
        return MappingClass::ORDER_BY;
    }
}
