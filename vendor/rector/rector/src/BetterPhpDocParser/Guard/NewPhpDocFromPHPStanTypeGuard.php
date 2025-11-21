<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\Guard;

use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
final class NewPhpDocFromPHPStanTypeGuard
{
    public function isLegal(Type $type) : bool
    {
        if ($type instanceof UnionType) {
            return $this->isLegalUnionType($type);
        }
        return \true;
    }
    private function isLegalUnionType(UnionType $type) : bool
    {
        foreach ($type->getTypes() as $unionType) {
            if ($unionType instanceof MixedType) {
                return \false;
            }
        }
        return \true;
    }
}
