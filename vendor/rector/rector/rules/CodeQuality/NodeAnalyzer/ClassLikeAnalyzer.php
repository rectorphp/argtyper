<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
final class ClassLikeAnalyzer
{
    /**
     * @return string[]
     */
    public function resolvePropertyNames(Class_ $class): array
    {
        $propertyNames = [];
        foreach ($class->getProperties() as $property) {
            foreach ($property->props as $prop) {
                $propertyNames[] = $prop->name->toString();
            }
        }
        return $propertyNames;
    }
}
