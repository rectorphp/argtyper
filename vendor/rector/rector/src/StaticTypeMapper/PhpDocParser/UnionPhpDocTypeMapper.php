<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\PhpDocParser;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Analyser\NameScope;
use Argtyper202511\PHPStan\PhpDoc\TypeNodeResolver;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IntersectionTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;
/**
 * @implements PhpDocTypeMapperInterface<UnionTypeNode>
 */
final class UnionPhpDocTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper
     */
    private $identifierPhpDocTypeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDocParser\IntersectionPhpDocTypeMapper
     */
    private $intersectionPhpDocTypeMapper;
    /**
     * @readonly
     * @var \PHPStan\PhpDoc\TypeNodeResolver
     */
    private $typeNodeResolver;
    public function __construct(TypeFactory $typeFactory, \Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper $identifierPhpDocTypeMapper, \Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\IntersectionPhpDocTypeMapper $intersectionPhpDocTypeMapper, TypeNodeResolver $typeNodeResolver)
    {
        $this->typeFactory = $typeFactory;
        $this->identifierPhpDocTypeMapper = $identifierPhpDocTypeMapper;
        $this->intersectionPhpDocTypeMapper = $intersectionPhpDocTypeMapper;
        $this->typeNodeResolver = $typeNodeResolver;
    }
    public function getNodeType(): string
    {
        return UnionTypeNode::class;
    }
    /**
     * @param UnionTypeNode $typeNode
     */
    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        $unionedTypes = [];
        foreach ($typeNode->types as $unionedTypeNode) {
            if ($unionedTypeNode instanceof IdentifierTypeNode) {
                $unionedTypes[] = $this->identifierPhpDocTypeMapper->mapToPHPStanType($unionedTypeNode, $node, $nameScope);
                continue;
            }
            if ($unionedTypeNode instanceof IntersectionTypeNode) {
                $unionedTypes[] = $this->intersectionPhpDocTypeMapper->mapToPHPStanType($unionedTypeNode, $node, $nameScope);
                continue;
            }
            $unionedTypes[] = $this->typeNodeResolver->resolve($unionedTypeNode, $nameScope);
        }
        // to prevent missing class error, e.g. in tests
        return $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($unionedTypes);
    }
}
