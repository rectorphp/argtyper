<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\NodeFactory;

use Argtyper202511\PhpParser\Modifiers;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\PropertyItem;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\CodeQuality\ValueObject\DefinedPropertyWithType;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
final class MissingPropertiesFactory
{
    /**
     * @readonly
     * @var \Rector\CodeQuality\NodeFactory\PropertyTypeDecorator
     */
    private $propertyTypeDecorator;
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(\Argtyper202511\Rector\CodeQuality\NodeFactory\PropertyTypeDecorator $propertyTypeDecorator, PhpVersionProvider $phpVersionProvider, StaticTypeMapper $staticTypeMapper)
    {
        $this->propertyTypeDecorator = $propertyTypeDecorator;
        $this->phpVersionProvider = $phpVersionProvider;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param DefinedPropertyWithType[] $definedPropertiesWithType
     * @return Property[]
     */
    public function create(array $definedPropertiesWithType): array
    {
        $newProperties = [];
        foreach ($definedPropertiesWithType as $definedPropertyWithType) {
            $visibilityModifier = $this->isFromAlwaysDefinedMethod($definedPropertyWithType) ? Modifiers::PRIVATE : Modifiers::PUBLIC;
            $property = new Property($visibilityModifier, [new PropertyItem($definedPropertyWithType->getName())]);
            if ($this->isFromAlwaysDefinedMethod($definedPropertyWithType) && $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
                $propertyType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($definedPropertyWithType->getType(), TypeKind::PROPERTY);
                if ($propertyType instanceof Node) {
                    $property->type = $propertyType;
                    $newProperties[] = $property;
                    continue;
                }
            }
            // fallback to docblock
            $this->propertyTypeDecorator->decorateProperty($property, $definedPropertyWithType->getType());
            $newProperties[] = $property;
        }
        return $newProperties;
    }
    private function isFromAlwaysDefinedMethod(DefinedPropertyWithType $definedPropertyWithType): bool
    {
        return in_array($definedPropertyWithType->getDefinedInMethodName(), [MethodName::CONSTRUCT, MethodName::SET_UP], \true);
    }
}
