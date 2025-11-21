<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Stringable;
final class BracketsAwareIntersectionTypeNode extends IntersectionTypeNode
{
    public function __toString(): string
    {
        return implode('&', $this->types);
    }
}
