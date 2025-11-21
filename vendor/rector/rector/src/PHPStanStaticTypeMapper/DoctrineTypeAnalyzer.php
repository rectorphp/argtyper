<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
final class DoctrineTypeAnalyzer
{
    public function isDoctrineCollectionWithIterableUnionType(Type $type): bool
    {
        if (!$type instanceof UnionType) {
            return \false;
        }
        $isArrayType = \false;
        $hasDoctrineCollectionType = \false;
        foreach ($type->getTypes() as $unionedType) {
            if ($this->isInstanceOfCollectionType($unionedType)) {
                $hasDoctrineCollectionType = \true;
            }
            if ($unionedType->isArray()->yes()) {
                $isArrayType = \true;
            }
        }
        if (!$hasDoctrineCollectionType) {
            return \false;
        }
        return $isArrayType;
    }
    public function isInstanceOfCollectionType(Type $type): bool
    {
        if (!$type instanceof ObjectType) {
            return \false;
        }
        return $type->isInstanceOf('Argtyper202511\Doctrine\Common\Collections\Collection')->yes();
    }
}
