<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\AttributeTransformer;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\ClassAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\Contract\PropertyAttributeTransformerInterface;
use Argtyper202511\Rector\Doctrine\CodeQuality\ValueObject\EntityMapping;
use Argtyper202511\Rector\ValueObject\MethodName;
final class YamlToAttributeTransformer
{
    /**
     * @var ClassAttributeTransformerInterface[]
     * @readonly
     */
    private $classAttributeTransformers;
    /**
     * @var PropertyAttributeTransformerInterface[]
     * @readonly
     */
    private $propertyAttributeTransformers;
    /**
     * @param ClassAttributeTransformerInterface[] $classAttributeTransformers
     * @param PropertyAttributeTransformerInterface[] $propertyAttributeTransformers
     */
    public function __construct(iterable $classAttributeTransformers, iterable $propertyAttributeTransformers)
    {
        $this->classAttributeTransformers = $classAttributeTransformers;
        $this->propertyAttributeTransformers = $propertyAttributeTransformers;
    }
    public function transform(Class_ $class, EntityMapping $entityMapping) : bool
    {
        $hasTransformedClass = $this->transformClass($class, $entityMapping);
        $hasTransformedProperties = $this->transformProperties($class, $entityMapping);
        return $hasTransformedClass || $hasTransformedProperties;
    }
    private function transformClass(Class_ $class, EntityMapping $entityMapping) : bool
    {
        $hasChanged = \false;
        foreach ($this->classAttributeTransformers as $classAttributeTransformer) {
            if ($this->hasAttribute($class, $classAttributeTransformer->getClassName())) {
                continue;
            }
            $hasTransformedAttribute = $classAttributeTransformer->transform($entityMapping, $class);
            if ($hasTransformedAttribute) {
                $hasChanged = \true;
            }
        }
        return $hasChanged;
    }
    private function transformProperties(Class_ $class, EntityMapping $entityMapping) : bool
    {
        $hasChanged = \false;
        foreach ($class->getProperties() as $property) {
            foreach ($this->propertyAttributeTransformers as $propertyAttributeTransformer) {
                if ($this->hasAttribute($property, $propertyAttributeTransformer->getClassName())) {
                    continue;
                }
                $hasTransformedAttribute = $propertyAttributeTransformer->transform($entityMapping, $property);
                if ($hasTransformedAttribute) {
                    $hasChanged = \true;
                }
            }
        }
        // handle promoted properties
        $constructorClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructorClassMethod instanceof ClassMethod) {
            return $hasChanged;
        }
        foreach ($constructorClassMethod->getParams() as $param) {
            // is promoted property?
            if ($param->flags === 0) {
                continue;
            }
            foreach ($this->propertyAttributeTransformers as $propertyAttributeTransformer) {
                if ($this->hasAttribute($param, $propertyAttributeTransformer->getClassName())) {
                    continue;
                }
                $hasTransformedAttribute = $propertyAttributeTransformer->transform($entityMapping, $param);
                if ($hasTransformedAttribute) {
                    $hasChanged = \true;
                }
            }
        }
        return $hasChanged;
    }
    /**
     * @param \PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Param $stmt
     */
    private function hasAttribute($stmt, string $attributeClassName) : bool
    {
        foreach ($stmt->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === $attributeClassName) {
                    return \true;
                }
            }
        }
        return \false;
    }
}
