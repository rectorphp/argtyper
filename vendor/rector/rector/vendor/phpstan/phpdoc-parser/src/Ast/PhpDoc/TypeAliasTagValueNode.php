<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class TypeAliasTagValueNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @var string
     */
    public $alias;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $type;
    public function __construct(string $alias, TypeNode $type)
    {
        $this->alias = $alias;
        $this->type = $type;
    }
    public function __toString(): string
    {
        return trim("{$this->alias} {$this->type}");
    }
}
