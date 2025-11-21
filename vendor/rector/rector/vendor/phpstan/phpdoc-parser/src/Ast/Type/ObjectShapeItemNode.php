<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprStringNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ObjectShapeItemNode implements Node
{
    use NodeAttributes;
    /** @var ConstExprStringNode|IdentifierTypeNode */
    public $keyName;
    /**
     * @var bool
     */
    public $optional;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $valueType;
    /**
     * @param ConstExprStringNode|IdentifierTypeNode $keyName
     */
    public function __construct($keyName, bool $optional, \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $valueType)
    {
        $this->keyName = $keyName;
        $this->optional = $optional;
        $this->valueType = $valueType;
    }
    public function __toString(): string
    {
        if ($this->keyName !== null) {
            return sprintf('%s%s: %s', (string) $this->keyName, $this->optional ? '?' : '', (string) $this->valueType);
        }
        return (string) $this->valueType;
    }
}
