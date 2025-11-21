<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\TypeNodeAnalyzer;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\SpacingAwareArrayTypeNode;
final class MixedArrayTypeNodeAnalyzer
{
    public function hasMixedArrayType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode): bool
    {
        $types = $bracketsAwareUnionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof SpacingAwareArrayTypeNode) {
                $typeNode = $type->type;
                if (!$typeNode instanceof IdentifierTypeNode) {
                    continue;
                }
                if ($typeNode->name === 'mixed') {
                    return \true;
                }
            }
        }
        return \false;
    }
}
