<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\Contract\PhpDocParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Analyser\NameScope;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Type;
/**
 * @template TTypeNode as TypeNode
 */
interface PhpDocTypeMapperInterface
{
    /**
     * @return class-string<TTypeNode>
     */
    public function getNodeType() : string;
    /**
     * @param TTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope) : Type;
}
