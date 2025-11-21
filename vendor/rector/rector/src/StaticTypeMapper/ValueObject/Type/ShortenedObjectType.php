<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type;

use Argtyper202511\PHPStan\Type\IsSuperTypeOfResult;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
/**
 * @api
 */
final class ShortenedObjectType extends ObjectType
{
    /**
     * @var class-string
     * @readonly
     */
    private $fullyQualifiedName;
    /**
     * @param class-string $fullyQualifiedName
     */
    public function __construct(string $shortName, string $fullyQualifiedName)
    {
        $this->fullyQualifiedName = $fullyQualifiedName;
        parent::__construct($shortName);
    }
    public function isSuperTypeOf(Type $type) : IsSuperTypeOfResult
    {
        $fullyQualifiedObjectType = new ObjectType($this->fullyQualifiedName);
        return $fullyQualifiedObjectType->isSuperTypeOf($type);
    }
    public function getShortName() : string
    {
        return $this->getClassName();
    }
    /**
     * @return class-string
     */
    public function getFullyQualifiedName() : string
    {
        return $this->fullyQualifiedName;
    }
}
