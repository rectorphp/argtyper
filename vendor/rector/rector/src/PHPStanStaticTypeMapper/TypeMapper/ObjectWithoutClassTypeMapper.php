<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\TypeMapper;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use Argtyper202511\PHPStan\Type\Accessory\HasMethodType;
use Argtyper202511\PHPStan\Type\Accessory\HasPropertyType;
use Argtyper202511\PHPStan\Type\ObjectWithoutClassType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\ObjectWithoutClassTypeWithParentTypes;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @implements TypeMapperInterface<ObjectWithoutClassType>
 */
final class ObjectWithoutClassTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    public function getNodeClass() : string
    {
        return ObjectWithoutClassType::class;
    }
    /**
     * @param ObjectWithoutClassType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        return $type->toPhpDocNode();
    }
    /**
     * @param ObjectWithoutClassType|HasMethodType|HasPropertyType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        // special case for anonymous classes that implement another type
        if ($type instanceof ObjectWithoutClassTypeWithParentTypes) {
            $parentTypes = $type->getParentTypes();
            if (\count($parentTypes) === 1) {
                $parentType = $parentTypes[0];
                return new FullyQualified($parentType->getClassName());
            }
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::OBJECT_TYPE)) {
            return null;
        }
        return new Identifier('object');
    }
}
