<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<MixedType>
 */
final class MixedTypeMapper implements TypeMapperInterface
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
        return MixedType::class;
    }
    /**
     * @param MixedType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param MixedType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::MIXED_TYPE)) {
            return null;
        }
        if (!$type->isExplicitMixed()) {
            return null;
        }
        if ($typeKind === TypeKind::UNION) {
            return null;
        }
        return new Identifier('mixed');
    }
}
