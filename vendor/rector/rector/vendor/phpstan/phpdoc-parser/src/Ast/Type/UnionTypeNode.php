<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function array_map;
use function implode;
class UnionTypeNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /** @var TypeNode[] */
    public $types;
    /**
     * @param TypeNode[] $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }
    public function __toString(): string
    {
        return '(' . implode(' | ', array_map(static function (\Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $type): string {
            if ($type instanceof \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\NullableTypeNode) {
                return '(' . $type . ')';
            }
            return (string) $type;
        }, $this->types)) . ')';
    }
}
