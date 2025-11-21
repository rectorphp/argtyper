<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use function implode;
class CallableTypeNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode
     */
    public $identifier;
    /** @var TemplateTagValueNode[] */
    public $templateTypes;
    /** @var CallableTypeParameterNode[] */
    public $parameters;
    /**
     * @var \PHPStan\PhpDocParser\Ast\Type\TypeNode
     */
    public $returnType;
    /**
     * @param CallableTypeParameterNode[] $parameters
     * @param TemplateTagValueNode[]  $templateTypes
     */
    public function __construct(\Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode $identifier, array $parameters, \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode $returnType, array $templateTypes)
    {
        $this->identifier = $identifier;
        $this->parameters = $parameters;
        $this->returnType = $returnType;
        $this->templateTypes = $templateTypes;
    }
    public function __toString(): string
    {
        $returnType = $this->returnType;
        if ($returnType instanceof self) {
            $returnType = "({$returnType})";
        }
        $template = $this->templateTypes !== [] ? '<' . implode(', ', $this->templateTypes) . '>' : '';
        $parameters = implode(', ', $this->parameters);
        return "{$this->identifier}{$template}({$parameters}): {$returnType}";
    }
}
