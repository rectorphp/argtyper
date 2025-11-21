<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Node as AstNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\ClosureType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\Type\FullyQualifiedIdentifierTypeNode;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<ClosureType>
 */
final class ClosureTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getNodeClass() : string
    {
        return ClosureType::class;
    }
    /**
     * @param ClosureType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        $typeNode = $type->toPhpDocNode();
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($typeNode, '', static function (AstNode $astNode) : ?FullyQualifiedIdentifierTypeNode {
            if (!$astNode instanceof IdentifierTypeNode) {
                return null;
            }
            if ($astNode->name !== 'Closure') {
                return null;
            }
            return new FullyQualifiedIdentifierTypeNode('Closure');
        });
        return $typeNode;
    }
    /**
     * @param TypeKind::* $typeKind
     * @param ClosureType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        // ref https://3v4l.org/iKMK6#v5.3.29
        if ($typeKind === TypeKind::PARAM && $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::ANONYMOUS_FUNCTION_PARAM_TYPE)) {
            return new FullyQualified('Closure');
        }
        // ref https://3v4l.org/g8WvW#v7.4.0
        if ($typeKind === TypeKind::PROPERTY && $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return new FullyQualified('Closure');
        }
        // ref https://3v4l.org/nUreN#v7.0.0
        if ($typeKind === TypeKind::RETURN && $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::ANONYMOUS_FUNCTION_RETURN_TYPE)) {
            return new FullyQualified('Closure');
        }
        // ref https://3v4l.org/ruh5g#v8.0.0
        if ($typeKind === TypeKind::UNION && $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES)) {
            return new FullyQualified('Closure');
        }
        return null;
    }
}
