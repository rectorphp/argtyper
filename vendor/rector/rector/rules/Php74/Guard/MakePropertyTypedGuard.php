<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php74\Guard;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
final class MakePropertyTypedGuard
{
    /**
     * @readonly
     * @var \Rector\Php74\Guard\PropertyTypeChangeGuard
     */
    private $propertyTypeChangeGuard;
    public function __construct(\Argtyper202511\Rector\Php74\Guard\PropertyTypeChangeGuard $propertyTypeChangeGuard)
    {
        $this->propertyTypeChangeGuard = $propertyTypeChangeGuard;
    }
    public function isLegal(Property $property, ClassReflection $classReflection, bool $inlinePublic = \true) : bool
    {
        if ($property->type instanceof Node) {
            return \false;
        }
        return $this->propertyTypeChangeGuard->isLegal($property, $classReflection, $inlinePublic);
    }
}
