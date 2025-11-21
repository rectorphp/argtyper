<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Accessory\NonEmptyArrayType;
use Argtyper202511\PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<NonEmptyArrayType>
 */
final class NonEmptyArrayTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return NonEmptyArrayType::class;
    }
    /**
     * @param NonEmptyArrayType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param NonEmptyArrayType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?\Argtyper202511\PhpParser\Node
    {
        return new Identifier('array');
    }
}
