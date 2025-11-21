<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Accessory\HasOffsetValueType;
use Argtyper202511\PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<HasOffsetValueType>
 */
final class HasOffsetValueTypeTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return HasOffsetValueType::class;
    }
    /**
     * @param HasOffsetValueType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param HasOffsetValueType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?\Argtyper202511\PhpParser\Node
    {
        return new Identifier('array');
    }
}
