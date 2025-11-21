<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\PhpDocParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Analyser\NameScope;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
/**
 * @implements PhpDocTypeMapperInterface<IntersectionTypeNode>
 */
final class IntersectionPhpDocTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper
     */
    private $identifierPhpDocTypeMapper;
    public function __construct(\Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper $identifierPhpDocTypeMapper)
    {
        $this->identifierPhpDocTypeMapper = $identifierPhpDocTypeMapper;
    }
    public function getNodeType(): string
    {
        return IntersectionTypeNode::class;
    }
    /**
     * @param IntersectionTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        $intersectionedTypes = [];
        foreach ($typeNode->types as $intersectionedTypeNode) {
            if (!$intersectionedTypeNode instanceof IdentifierTypeNode) {
                return new MixedType();
            }
            $intersectionedTypes[] = $this->identifierPhpDocTypeMapper->mapIdentifierTypeNode($intersectionedTypeNode, $node);
        }
        return new IntersectionType($intersectionedTypes);
    }
}
