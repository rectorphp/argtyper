<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeAnalyzer;

use Argtyper202511\PHPStan\Type\Generic\GenericClassStringType;
use Argtyper202511\PHPStan\Type\UnionType;
final class GenericClassStringTypeNormalizer
{
    public function isAllGenericClassStringType(UnionType $unionType) : bool
    {
        foreach ($unionType->getTypes() as $type) {
            if (!$type instanceof GenericClassStringType) {
                return \false;
            }
        }
        return \true;
    }
}
