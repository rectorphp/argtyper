<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\TypeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
final class ArrayTypeAnalyzer
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
    public function isArrayType(Expr $expr) : bool
    {
        $nodeType = $this->nodeTypeResolver->getNativeType($expr);
        return $nodeType->isArray()->yes();
    }
}
