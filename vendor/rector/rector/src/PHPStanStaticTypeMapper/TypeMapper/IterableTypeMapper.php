<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\IterableType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<IterableType>
 */
final class IterableTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return IterableType::class;
    }
    /**
     * @param IterableType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param IterableType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?\Argtyper202511\PhpParser\Node
    {
        return new Identifier('iterable');
    }
}
