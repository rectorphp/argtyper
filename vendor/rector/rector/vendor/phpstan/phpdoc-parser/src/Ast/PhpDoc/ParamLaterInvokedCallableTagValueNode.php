<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function trim;
class ParamLaterInvokedCallableTagValueNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode
{
    use NodeAttributes;
    /**
     * @var string
     */
    public $parameterName;
    /** @var string (may be empty) */
    public $description;
    public function __construct(string $parameterName, string $description)
    {
        $this->parameterName = $parameterName;
        $this->description = $description;
    }
    public function __toString(): string
    {
        return trim("{$this->parameterName} {$this->description}");
    }
}
