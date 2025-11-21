<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
final class NullableTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveNullableObjectType(Expr $expr): ?\Argtyper202511\PHPStan\Type\ObjectType
    {
        $exprType = $this->nodeTypeResolver->getNativeType($expr);
        $baseType = TypeCombinator::removeNull($exprType);
        if (!$baseType instanceof ObjectType) {
            return null;
        }
        return $baseType;
    }
}
