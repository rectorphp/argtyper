<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class InverseJoinColumnAttributeTransformer implements PropertyAttributeTransformerInterface
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
        $joinTableMapping = $entityMapping->matchManyToManyPropertyMapping($property)['joinTable'] ?? null;
        if (!\is_array($joinTableMapping)) {
            return \false;
        }
        $joinColumns = $joinTableMapping['inverseJoinColumns'] ?? null;
        if (!\is_array($joinColumns)) {
            return \false;
        }
        foreach ($joinColumns as $columnName => $joinColumn) {
            $property->attrGroups[] = $this->createInverseJoinColumnAttrGroup($columnName, $joinColumn);
        }
        return \true;
    }
    public function getClassName() : string
    {
        return MappingClass::INVERSE_JOIN_COLUMN;
    }
    /**
     * @param int|string $columnName
     * @param mixed $joinColumn
     */
    private function createInverseJoinColumnAttrGroup($columnName, $joinColumn) : AttributeGroup
    {
        $joinColumn = \array_merge(['name' => $columnName], $joinColumn);
        $args = $this->nodeFactory->createArgs($joinColumn);
        return AttributeFactory::createGroup($this->getClassName(), $args);
    }
}
