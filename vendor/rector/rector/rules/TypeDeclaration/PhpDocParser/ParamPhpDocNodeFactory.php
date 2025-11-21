<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\PhpDocParser;

use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class ParamPhpDocNodeFactory
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
    public function create(TypeNode $typeNode, Param $param): ParamTagValueNode
    {
        return new ParamTagValueNode($typeNode, $param->variadic, '$' . $this->nodeNameResolver->getName($param), '', \false);
    }
}
