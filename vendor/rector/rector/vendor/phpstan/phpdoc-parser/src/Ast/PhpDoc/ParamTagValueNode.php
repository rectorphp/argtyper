<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use function trim;
class ParamTagValueNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $type;
    /**
     * @var bool
     */
    public $isReference;
    /**
     * @var bool
     */
    public $isVariadic;
    /**
     * @var string
     */
    public $parameterName;
    /** @var string (may be empty) */
    public $description;
    public function __construct(TypeNode $type, bool $isVariadic, string $parameterName, string $description, bool $isReference)
    {
        $this->type = $type;
        $this->isReference = $isReference;
        $this->isVariadic = $isVariadic;
        $this->parameterName = $parameterName;
        $this->description = $description;
    }
    public function __toString() : string
    {
        $reference = $this->isReference ? '&' : '';
        $variadic = $this->isVariadic ? '...' : '';
        return trim("{$this->type} {$reference}{$variadic}{$this->parameterName} {$this->description}");
    }
}
