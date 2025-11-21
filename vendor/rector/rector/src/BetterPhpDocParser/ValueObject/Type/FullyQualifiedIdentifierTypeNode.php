<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Stringable;
final class FullyQualifiedIdentifierTypeNode extends IdentifierTypeNode
{
    public function __toString() : string
    {
        return '\\' . \ltrim($this->name, '\\');
    }
}
