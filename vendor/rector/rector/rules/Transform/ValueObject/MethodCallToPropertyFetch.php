<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Transform\ValueObject;

use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Validation\RectorAssert;
/**
 * @deprecated as related rule is deprecated
 */
final class MethodCallToPropertyFetch
{
    /**
     * @readonly
     * @var string
     */
    private $oldType;
    /**
     * @readonly
     * @var string
     */
    private $oldMethod;
    /**
     * @readonly
     * @var string
     */
    private $newProperty;
    public function __construct(string $oldType, string $oldMethod, string $newProperty)
    {
        $this->oldType = $oldType;
        $this->oldMethod = $oldMethod;
        $this->newProperty = $newProperty;
        RectorAssert::className($oldType);
        RectorAssert::methodName($oldMethod);
        RectorAssert::propertyName($newProperty);
    }
    public function getOldObjectType() : ObjectType
    {
        return new ObjectType($this->oldType);
    }
    public function getNewProperty() : string
    {
        return $this->newProperty;
    }
    public function getOldMethod() : string
    {
        return $this->oldMethod;
    }
}
