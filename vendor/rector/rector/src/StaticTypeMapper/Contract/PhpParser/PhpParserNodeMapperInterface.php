<?php

declare (strict_types=1);
namespace Rector\StaticTypeMapper\Contract\PhpParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Type\Type;
/**
 * @template TNode as \PhpParser\Node
 */
interface PhpParserNodeMapperInterface
{
    /**
     * @return class-string<TNode>
     */
    public function getNodeType(): string;
    /**
     * @param TNode $node
     */
    public function mapToPHPStan(Node $node): Type;
}
