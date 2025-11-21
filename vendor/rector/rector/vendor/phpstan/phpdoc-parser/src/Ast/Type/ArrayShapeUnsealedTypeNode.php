<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ArrayShapeUnsealedTypeNode implements Node
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $valueType;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode|null
     */
    public $keyType;
    public function __construct(\Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $valueType, ?\Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $keyType)
    {
        $this->valueType = $valueType;
        $this->keyType = $keyType;
    }
    public function __toString() : string
    {
        if ($this->keyType !== null) {
            return sprintf('<%s, %s>', $this->keyType, $this->valueType);
        }
        return sprintf('<%s>', $this->valueType);
    }
}
