<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
class ConstExprFalseNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
{
    use NodeAttributes;
    public function __toString(): string
    {
        return 'false';
    }
}
