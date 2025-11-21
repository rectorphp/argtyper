<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use function sprintf;
class ConditionalTypeNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $subjectType;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $targetType;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $if;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $else;
    /**
     * @var bool
     */
    public $negated;
    public function __construct(\Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $subjectType, \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $targetType, \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $if, \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $else, bool $negated)
    {
        $this->subjectType = $subjectType;
        $this->targetType = $targetType;
        $this->if = $if;
        $this->else = $else;
        $this->negated = $negated;
    }
    public function __toString(): string
    {
        return sprintf('(%s %s %s ? %s : %s)', $this->subjectType, $this->negated ? 'is not' : 'is', $this->targetType, $this->if, $this->else);
    }
}
