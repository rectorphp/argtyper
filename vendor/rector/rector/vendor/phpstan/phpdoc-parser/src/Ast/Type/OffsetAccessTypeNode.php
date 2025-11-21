<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
class OffsetAccessTypeNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $type;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $offset;
    public function __construct(\Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $type, \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $offset)
    {
        $this->type = $type;
        $this->offset = $offset;
    }
    public function __toString() : string
    {
        if ($this->type instanceof \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\CallableTypeNode || $this->type instanceof \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\NullableTypeNode) {
            return '(' . $this->type . ')[' . $this->offset . ']';
        }
        return $this->type . '[' . $this->offset . ']';
    }
}
