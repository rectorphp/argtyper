<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineTagValueNode;
use function trim;
class PhpDocTagNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocChildNode
{
    use NodeAttributes;
    /**
     * @var string
     */
    public $name;
    /**
     * @var \PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
     */
    public $value;
    public function __construct(string $name, \Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode $value)
    {
        $this->name = $name;
        $this->value = $value;
    }
    public function __toString(): string
    {
        if ($this->value instanceof DoctrineTagValueNode) {
            return (string) $this->value;
        }
        return trim("{$this->name} {$this->value}");
    }
}
