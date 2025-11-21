<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type;

use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\PHPStan\Type\IsSuperTypeOfResult;
use Argtyper202511\PHPStan\Type\Type;
/**
 * @api
 */
final class ShortenedGenericObjectType extends GenericObjectType
{
    /**
     * @var class-string
     * @readonly
     */
    private $fullyQualifiedName;
    /**
     * @param class-string $fullyQualifiedName
     */
    public function __construct(string $shortName, array $types, string $fullyQualifiedName)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        parent::__construct($shortName, $types);
    }
    public function isSuperTypeOf(Type $type): IsSuperTypeOfResult
    {
        $genericObjectType = new GenericObjectType($this->fullyQualifiedName, $this->getTypes());
        return $genericObjectType->isSuperTypeOf($type);
    }
    public function getShortName(): string
    {
        return $this->getClassName();
    }
}
