<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Accessory\OversizedArrayType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @implements TypeMapperInterface<OversizedArrayType>
 */
final class OversizedArrayTypeMapper implements TypeMapperInterface
{
    public function getNodeClass() : string
    {
        return OversizedArrayType::class;
    }
    /**
     * @param OversizedArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param TypeKind::* $typeKind
     * @param OversizedArrayType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?\Argtyper202511\PhpParser\Node
    {
        return new Identifier('array');
    }
}
