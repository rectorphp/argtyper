<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\Enum\EntityMappingKey;
use Argtyper202511\Rector\Doctrine\CodeQuality\Helper\NodeValueNormalizer;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class ManyToManyAttributeTransformer implements PropertyAttributeTransformerInterface
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
        $manyToManyMapping = $entityMapping->matchManyToManyPropertyMapping($property);
        if (!\is_array($manyToManyMapping)) {
            return \false;
        }
        // handled by another mapper
        unset($manyToManyMapping['joinTable']);
        $args = $this->nodeFactory->createArgs($manyToManyMapping);
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
        NodeValueNormalizer::ensureKeyIsClassConstFetch($args, EntityMappingKey::TARGET_ENTITY);
        return \true;
    }
    public function getClassName() : string
    {
        return MappingClass::MANY_TO_MANY;
    }
}
