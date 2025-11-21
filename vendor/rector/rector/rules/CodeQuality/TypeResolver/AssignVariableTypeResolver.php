<?php

declare (strict_types=1);
namespace Rector\CodeQuality\TypeResolver;

use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class AssignVariableTypeResolver
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
    public function resolve(Assign $assign): Type
    {
        $exprType = $this->nodeTypeResolver->getType($assign->expr);
        if ($exprType instanceof UnionType) {
            return $exprType;
        }
        return $this->nodeTypeResolver->getType($assign->var);
    }
}
