<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\TypeNodeAnalyzer;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\BracketsAwareUnionTypeNode;
final class GenericTypeNodeAnalyzer
{
    public function hasGenericType(BracketsAwareUnionTypeNode $bracketsAwareUnionTypeNode): bool
    {
        $types = $bracketsAwareUnionTypeNode->types;
        foreach ($types as $type) {
            if ($type instanceof GenericTypeNode) {
                return \true;
            }
        }
        return \false;
    }
}
