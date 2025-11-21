<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstTypeNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
     */
    public $constExpr;
    public function __construct(ConstExprNode $constExpr)
    {
        $this->constExpr = $constExpr;
    }
    public function __toString(): string
    {
        return $this->constExpr->__toString();
    }
}
