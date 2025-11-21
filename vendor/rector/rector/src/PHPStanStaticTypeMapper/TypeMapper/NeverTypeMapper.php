<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\NeverType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<NeverType>
 */
final class NeverTypeMapper implements TypeMapperInterface
{
    public function getNodeClass() : string
    {
        return NeverType::class;
    }
    /**
     * @param NeverType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param NeverType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        return null;
    }
}
