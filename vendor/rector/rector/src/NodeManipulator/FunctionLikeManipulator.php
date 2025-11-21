<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeManipulator;

use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class FunctionLikeManipulator
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @return string[]
     */
    public function resolveParamNames(FunctionLike $functionLike) : array
    {
        $paramNames = [];
        foreach ($functionLike->getParams() as $param) {
            $paramNames[] = $this->nodeNameResolver->getName($param);
        }
        return $paramNames;
    }
}
