<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Accessory\HasMethodType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
/**
 * @implements TypeMapperInterface<HasMethodType>
 */
final class HasMethodTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper
     */
    private $objectWithoutClassTypeMapper;
    public function __construct(\Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper\ObjectWithoutClassTypeMapper $objectWithoutClassTypeMapper)
    {
        $this->objectWithoutClassTypeMapper = $objectWithoutClassTypeMapper;
    }
    public function getNodeClass() : string
    {
        return HasMethodType::class;
    }
    /**
     * @param HasMethodType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param HasMethodType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        return $this->objectWithoutClassTypeMapper->mapToPhpParserNode($type, $typeKind);
    }
}
