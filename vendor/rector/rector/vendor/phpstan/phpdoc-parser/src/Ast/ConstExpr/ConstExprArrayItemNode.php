<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ConstExprArrayItemNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode|null
     */
    public $key;
    /**
     * @var \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
     */
    public $value;
    public function __construct(?\Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $key, \Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode $value)
    {
        $this->key = $key;
        $this->value = $value;
    }
    public function __toString(): string
    {
        if ($this->key !== null) {
            return sprintf('%s => %s', $this->key, $this->value);
        }
        return (string) $this->value;
    }
}
