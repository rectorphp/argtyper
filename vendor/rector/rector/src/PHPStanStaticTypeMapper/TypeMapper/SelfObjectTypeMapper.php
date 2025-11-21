<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\SelfObjectType;
/**
 * @implements TypeMapperInterface<SelfObjectType>
 */
final class SelfObjectTypeMapper implements TypeMapperInterface
{
    public function getNodeClass() : string
    {
        return SelfObjectType::class;
    }
    /**
     * @param SelfObjectType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param SelfObjectType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?\Argtyper202511\PhpParser\Node
    {
        return new Name('self');
    }
}
