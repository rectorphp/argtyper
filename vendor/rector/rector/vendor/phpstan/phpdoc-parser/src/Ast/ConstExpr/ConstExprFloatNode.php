<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstExprFloatNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
{
    use NodeAttributes;
    /**
     * @var string
     */
    public $value;
    public function __construct(string $value)
    {
        $this->value = $value;
    }
    public function __toString(): string
    {
        return $this->value;
    }
}
