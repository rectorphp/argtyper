<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\PropertyAttributeTransformer;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
final class IdAttributeTransformer implements PropertyAttributeTransformerInterface
{
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $property
     */
    public function transform(EntityMapping $entityMapping, $property): bool
    {
        $idMapping = $entityMapping->matchIdPropertyMapping($property);
        if (!is_array($idMapping)) {
            return \false;
        }
        $property->attrGroups[] = AttributeFactory::createGroup($this->getClassName());
        return \true;
    }
    public function getClassName(): string
    {
        return MappingClass::ID;
    }
}
