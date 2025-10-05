<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

/**
 * @api
 */
final class PrivatesAccessor
{
    public static function callMethod(object $object, string $methodName, mixed ...$args): mixed
    {
        $reflectionMethod = new \ReflectionMethod($object, $methodName);
        return $reflectionMethod->invoke($object, ...$args);
    }
}
