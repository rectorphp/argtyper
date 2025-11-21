<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer\ClassAttributeTransformer;

use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\NodeFactory\AttributeFactory;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\PhpParser\Node\NodeFactory;
final class InheritanceClassAttributeTransformer implements ClassAttributeTransformerInterface
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
    public function transform(EntityMapping $entityMapping, Class_ $class): bool
    {
        $classMapping = $entityMapping->getClassMapping();
        $inheritanceType = $classMapping['inheritanceType'] ?? null;
        if ($inheritanceType === null) {
            return \false;
        }
        $class->attrGroups[] = AttributeFactory::createGroup(MappingClass::INHERITANCE_TYPE, [$inheritanceType]);
        if (isset($classMapping['discriminatorColumn'])) {
            $class->attrGroups[] = AttributeFactory::createGroup(MappingClass::DISCRIMINATOR_COLUMN, $classMapping['discriminatorColumn']);
        }
        if (isset($classMapping['discriminatorMap'])) {
            $this->addDiscriminatorMap($classMapping['discriminatorMap'], $class);
        }
        return \true;
    }
    public function getClassName(): string
    {
        return MappingClass::DISCRIMINATOR_MAP;
    }
    /**
     * @param array<string, mixed> $discriminatorMap
     */
    private function addDiscriminatorMap(array $discriminatorMap, Class_ $class): void
    {
        $args = $this->nodeFactory->createArgs([$discriminatorMap]);
        foreach ($args as $arg) {
            if ($arg->value instanceof Array_) {
                $array = $arg->value;
                foreach ($array->items as $arrayItem) {
                    if (!$arrayItem instanceof ArrayItem) {
                        continue;
                    }
                    if (!$arrayItem->value instanceof String_) {
                        continue;
                    }
                    $string = $arrayItem->value;
                    $arrayItem->value = new ClassConstFetch(new FullyQualified($string->value), new Identifier('class'));
                }
            }
        }
        // @todo all value should be class const
        $class->attrGroups[] = AttributeFactory::createGroup(MappingClass::DISCRIMINATOR_MAP, $args);
    }
}
