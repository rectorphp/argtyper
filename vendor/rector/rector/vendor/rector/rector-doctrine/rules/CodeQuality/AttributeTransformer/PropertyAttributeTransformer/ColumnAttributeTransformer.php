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
final class ColumnAttributeTransformer implements PropertyAttributeTransformerInterface
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
    public function transform(EntityMapping $entityMapping, $property): bool
    {
        $propertyMapping = $entityMapping->matchFieldPropertyMapping($property);
        if ($propertyMapping === null) {
            return \false;
        }
        // handled in another mapper
        unset($propertyMapping['gedmo']);
        $args = [];
        // rename to "name"
        if (isset($propertyMapping[EntityMappingKey::COLUMN])) {
            $column = $propertyMapping[EntityMappingKey::COLUMN];
            $args[] = AttributeFactory::createNamedArg($column, EntityMappingKey::NAME);
            unset($propertyMapping[EntityMappingKey::COLUMN]);
        }
        $args = array_merge($args, $this->nodeFactory->createArgs($propertyMapping));
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName(), $args);
        return \true;
    }
    public function getClassName(): string
    {
        return MappingClass::COLUMN;
    }
}
