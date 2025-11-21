<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Renaming\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
final class RenameProperty
{
    /**
     * @readonly
     * @var string
     */
    private $type;
    /**
     * @readonly
     * @var string
     */
    private $oldProperty;
    /**
     * @readonly
     * @var string
     */
    private $newProperty;
    public function __construct(string $type, string $oldProperty, string $newProperty)
    {
        $this->type = $type;
        $this->oldProperty = $oldProperty;
        $this->newProperty = $newProperty;
        RectorAssert::className($type);
        RectorAssert::propertyName($oldProperty);
        RectorAssert::propertyName($newProperty);
    }
    public function getObjectType() : ObjectType
    {
        return new ObjectType($this->type);
    }
    public function getOldProperty() : string
    {
        return $this->oldProperty;
    }
    public function getNewProperty() : string
    {
        return $this->newProperty;
    }
}
