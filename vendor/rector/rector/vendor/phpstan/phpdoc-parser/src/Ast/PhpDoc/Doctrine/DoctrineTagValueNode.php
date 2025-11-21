<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use function trim;
class DoctrineTagValueNode implements PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineAnnotation
     */
    public $annotation;
    /** @var string (may be empty) */
    public $description;
    public function __construct(\Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\Doctrine\DoctrineAnnotation $annotation, string $description)
    {
        $this->annotation = $annotation;
        $this->description = $description;
    }
    public function __toString() : string
    {
        return trim("{$this->annotation} {$this->description}");
    }
}
