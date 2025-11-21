<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\Mapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Exception\NotImplementedYetException;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
final class PhpParserNodeMapper
{
    /**
     * @var PhpParserNodeMapperInterface[]
     * @readonly
     */
    private $phpParserNodeMappers;
    /**
     * @param PhpParserNodeMapperInterface[] $phpParserNodeMappers
     */
    public function __construct(iterable $phpParserNodeMappers)
    {
        $this->phpParserNodeMappers = $phpParserNodeMappers;
    }
    public function mapToPHPStanType(Node $node) : Type
    {
        foreach ($this->phpParserNodeMappers as $phpParserNodeMapper) {
            if (!\is_a($node, $phpParserNodeMapper->getNodeType())) {
                continue;
            }
            return $phpParserNodeMapper->mapToPHPStan($node);
        }
        throw new NotImplementedYetException(\get_class($node));
    }
}
