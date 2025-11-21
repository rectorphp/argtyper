<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Accessory\HasOffsetType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<HasOffsetType>
 */
final class HasOffsetTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return HasOffsetType::class;
    }
    /**
     * @param HasOffsetType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param HasOffsetType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?\Argtyper202511\PhpParser\Node
    {
        return new Identifier('array');
    }
}
