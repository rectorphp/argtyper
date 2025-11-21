<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\PhpParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
/**
 * @implements PhpParserNodeMapperInterface<Node\IntersectionType>
 */
final class IntersectionTypeNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper
     */
    private $fullyQualifiedNodeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpParser\NameNodeMapper
     */
    private $nameNodeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper
     */
    private $identifierNodeMapper;
    public function __construct(\Argtyper202511\Rector\StaticTypeMapper\PhpParser\FullyQualifiedNodeMapper $fullyQualifiedNodeMapper, \Argtyper202511\Rector\StaticTypeMapper\PhpParser\NameNodeMapper $nameNodeMapper, \Argtyper202511\Rector\StaticTypeMapper\PhpParser\IdentifierNodeMapper $identifierNodeMapper)
    {
        $this->fullyQualifiedNodeMapper = $fullyQualifiedNodeMapper;
        $this->nameNodeMapper = $nameNodeMapper;
        $this->identifierNodeMapper = $identifierNodeMapper;
    }
    public function getNodeType() : string
    {
        return Node\IntersectionType::class;
    }
    /**
     * @param Node\IntersectionType $node
     */
    public function mapToPHPStan(Node $node) : Type
    {
        $types = [];
        foreach ($node->types as $intersectionedType) {
            if ($intersectionedType instanceof FullyQualified) {
                $types[] = $this->fullyQualifiedNodeMapper->mapToPHPStan($intersectionedType);
                continue;
            }
            if ($intersectionedType instanceof Name) {
                $types[] = $this->nameNodeMapper->mapToPHPStan($intersectionedType);
                continue;
            }
            $types[] = $this->identifierNodeMapper->mapToPHPStan($intersectionedType);
        }
        return new IntersectionType($types);
    }
}
