<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Enum\ObjectReference;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ParentStaticType;
/**
 * @implements TypeMapperInterface<ParentStaticType>
 */
final class ParentStaticTypeMapper implements TypeMapperInterface
{
    public function getNodeClass() : string
    {
        return ParentStaticType::class;
    }
    /**
     * @param ParentStaticType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param ParentStaticType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?\Argtyper202511\PhpParser\Node
    {
        return new Name(ObjectReference::PARENT);
    }
}
