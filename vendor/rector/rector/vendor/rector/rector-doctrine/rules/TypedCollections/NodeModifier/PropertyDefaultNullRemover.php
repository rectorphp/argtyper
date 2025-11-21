<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\NodeModifier;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\Property;
final class PropertyDefaultNullRemover
{
    public function remove(Property $property): void
    {
        $soleProperty = $property->props[0];
        if (!$soleProperty->default instanceof Expr) {
            return;
        }
        $soleProperty->default = null;
    }
}
