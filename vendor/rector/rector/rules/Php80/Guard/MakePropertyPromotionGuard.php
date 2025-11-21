<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\Guard;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Php74\Guard\PropertyTypeChangeGuard;
final class MakePropertyPromotionGuard
{
    /**
     * @readonly
     * @var \Rector\Php74\Guard\PropertyTypeChangeGuard
     */
    private $propertyTypeChangeGuard;
    public function __construct(PropertyTypeChangeGuard $propertyTypeChangeGuard)
    {
        $this->propertyTypeChangeGuard = $propertyTypeChangeGuard;
    }
    public function isLegal(Class_ $class, ClassReflection $classReflection, Property $property, Param $param, bool $inlinePublic = \true): bool
    {
        if (!$this->propertyTypeChangeGuard->isLegal($property, $classReflection, $inlinePublic, \true)) {
            return \false;
        }
        if ($class->isFinal()) {
            return \true;
        }
        if ($inlinePublic) {
            return \true;
        }
        if ($property->isPrivate()) {
            return \true;
        }
        if (!$param->type instanceof Node) {
            return \true;
        }
        return $property->type instanceof Node;
    }
}
