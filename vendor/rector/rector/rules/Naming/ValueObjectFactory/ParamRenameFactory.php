<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\ValueObjectFactory;

use Argtyper202511\PhpParser\Node\Expr\Error;
use Argtyper202511\PhpParser\Node\FunctionLike;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\Rector\Naming\ValueObject\ParamRename;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class ParamRenameFactory
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
    public function createFromResolvedExpectedName(FunctionLike $functionLike, Param $param, string $expectedName): ?ParamRename
    {
        if ($param->var instanceof Error) {
            return null;
        }
        $currentName = $this->nodeNameResolver->getName($param);
        return new ParamRename($currentName, $expectedName, $param->var, $functionLike);
    }
}
