<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\ResourceType;
use Argtyper202511\PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<ResourceType>
 */
final class ResourceTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return ResourceType::class;
    }
    /**
     * @param ResourceType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param ResourceType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node
    {
        return null;
    }
}
