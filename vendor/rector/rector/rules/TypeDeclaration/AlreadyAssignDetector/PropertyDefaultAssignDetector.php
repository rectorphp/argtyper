<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\AlreadyAssignDetector;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\ClassLike;
use Argtyper202511\PhpParser\Node\Stmt\Property;
final class PropertyDefaultAssignDetector
{
    public function detect(ClassLike $classLike, string $propertyName) : bool
    {
        $property = $classLike->getProperty($propertyName);
        if (!$property instanceof Property) {
            return \false;
        }
        return $property->props[0]->default instanceof Expr;
    }
}
