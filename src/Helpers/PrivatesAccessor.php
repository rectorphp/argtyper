<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use ReflectionProperty;

/**
 * @api
 */
final class PrivatesAccessor
{
    public static function propertyClosure(object $object, string $propertyName, callable $closure): void
    {
        $property = self::getPrivateProperty($object, $propertyName);

        // modify value
        $property = $closure($property);

        self::setPrivateProperty($object, $propertyName, $property);
    }

    public static function callMethod(object $object, string $methodName, ...$args): mixed
    {
        $reflectionMethod = new \ReflectionMethod($object, $methodName);
        return $reflectionMethod->invoke($object, ...$args);
    }

    private static function getPrivateProperty(object $object, string $propertyName): mixed
    {
        $reflectionProperty = new ReflectionProperty($object, $propertyName);

        return $reflectionProperty->getValue($object);
    }

    private static function setPrivateProperty(object $object, string $propertyName, mixed $value): void
    {
        $reflectionProperty = new ReflectionProperty($object, $propertyName);
        $reflectionProperty->setValue($object, $value);
    }
}