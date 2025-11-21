<?php

declare (strict_types=1);
namespace Rector\CodeQuality\NodeFactory;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\ComplexType;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\PropertyItem;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PropertyTagValueNode;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\StaticTypeMapper;
final class TypedPropertyFactory
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function createFromPropertyTagValueNode(PropertyTagValueNode $propertyTagValueNode, Class_ $class, string $propertyName): Property
    {
        $propertyItem = new PropertyItem($propertyName);
        $propertyTypeNode = $this->createPropertyTypeNode($propertyTagValueNode, $class);
        return new Property(Modifiers::PRIVATE, [$propertyItem], [], $propertyTypeNode);
    }
    /**
     * @return \PhpParser\Node\Name|\PhpParser\Node\ComplexType|\PhpParser\Node\Identifier|null
     */
    public function createPropertyTypeNode(PropertyTagValueNode $propertyTagValueNode, Class_ $class, bool $isNullable = \true)
    {
        $propertyType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($propertyTagValueNode->type, $class);
        $typeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
        if ($isNullable && !$typeNode instanceof NullableType && !$typeNode instanceof ComplexType && $typeNode instanceof Node) {
            return new NullableType($typeNode);
        }
        return $typeNode;
    }
}
