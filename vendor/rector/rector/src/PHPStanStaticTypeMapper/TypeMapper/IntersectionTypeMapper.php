<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Node as AstNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\ArrayShapeItemNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\ObjectWithoutClassType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<IntersectionType>
 */
final class IntersectionTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper
     */
    private $objectWithoutClassTypeMapper;
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectTypeMapper
     */
    private $objectTypeMapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\Mapper\ScalarStringToTypeMapper
     */
    private $scalarStringToTypeMapper;
    public function __construct(PhpVersionProvider $phpVersionProvider, \Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper $objectWithoutClassTypeMapper, \Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectTypeMapper $objectTypeMapper, ScalarStringToTypeMapper $scalarStringToTypeMapper)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->objectWithoutClassTypeMapper = $objectWithoutClassTypeMapper;
        $this->objectTypeMapper = $objectTypeMapper;
        $this->scalarStringToTypeMapper = $scalarStringToTypeMapper;
    }
    public function getNodeClass(): string
    {
        return IntersectionType::class;
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $typeNode = $type->toPhpDocNode();
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($typeNode, '', function (AstNode $astNode) {
            if ($astNode instanceof UnionTypeNode) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($astNode instanceof ArrayShapeItemNode) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$astNode instanceof IdentifierTypeNode) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            $type = $this->scalarStringToTypeMapper->mapScalarStringToType($astNode->name);
            if ($type->isScalar()->yes()) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($type->isArray()->yes()) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if ($type instanceof MixedType && $type->isExplicitMixed()) {
                return PhpDocNodeTraverser::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            $astNode->name = '\\' . ltrim($astNode->name, '\\');
            return $astNode;
        });
        return $typeNode;
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::INTERSECTION_TYPES)) {
            return null;
        }
        $intersectionedTypeNodes = [];
        foreach ($type->getTypes() as $type) {
            if ($type instanceof ObjectWithoutClassType) {
                return $this->objectWithoutClassTypeMapper->mapToPhpParserNode($type, $typeKind);
            }
            if (!$type instanceof ObjectType) {
                return null;
            }
            $resolvedType = $this->objectTypeMapper->mapToPhpParserNode($type, $typeKind);
            if (!$resolvedType instanceof FullyQualified) {
                return null;
            }
            $intersectionedTypeNodes[] = $resolvedType;
        }
        if ($intersectionedTypeNodes === []) {
            return null;
        }
        if (count($intersectionedTypeNodes) === 1) {
            return current($intersectionedTypeNodes);
        }
        if ($typeKind === TypeKind::UNION && !$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_INTERSECTION_TYPES)) {
            return null;
        }
        return new Node\IntersectionType($intersectionedTypeNodes);
    }
}
