<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
final class EmbeddableClassAttributeTransformer implements ClassAttributeTransformerInterface
{
    public function transform(EntityMapping $entityMapping, Class_ $class): bool
    {
        $classMapping = $entityMapping->getClassMapping();
        $type = $classMapping['type'] ?? null;
        if ($type !== 'embeddable') {
            return \false;
        }
        $class->attrGroups[] = AttributeFactory::createGroup($this->getClassName());
        return \true;
    }
    public function getClassName(): string
    {
        return MappingClass::EMBEDDABLE;
    }
}
