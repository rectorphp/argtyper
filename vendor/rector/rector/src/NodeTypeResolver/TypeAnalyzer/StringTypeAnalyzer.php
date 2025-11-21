<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\TypeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
final class StringTypeAnalyzer
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
    public function isStringOrUnionStringOnlyType(Expr $expr) : bool
    {
        $nodeType = $this->nodeTypeResolver->getType($expr);
        return $nodeType->isString()->yes();
    }
}
