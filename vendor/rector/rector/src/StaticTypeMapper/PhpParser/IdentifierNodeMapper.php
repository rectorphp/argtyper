<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\PhpParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;
use Argtyper202511\Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
/**
 * @implements PhpParserNodeMapperInterface<Identifier>
 */
final class IdentifierNodeMapper implements PhpParserNodeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;
    public function __construct(ScalarStringToTypeMapper $scalarStringToTypeMapper)
    {
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
    }
    public function getNodeType(): string
    {
        return Identifier::class;
    }
    /**
     * @param Identifier $node
     */
    public function mapToPHPStan(Node $node): Type
    {
        return $this->scalarStringToTypeMapper->mapScalarStringToType($node->name);
    }
}
