<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;

final class ReflectionChecker
{
    public static function shouldSkipClassReflection(ClassReflection $classReflection, string $methodName): bool
    {
        if ($classReflection->isInternal()) {
            return true;
        }

        if (self::isVendor($classReflection)) {
            return true;
        }

        return ! $classReflection->hasMethod($methodName);
    }

    public static function shouldSkipFunctionReflection(FunctionReflection $functionReflection): bool
    {
        if ($functionReflection->isInternal()->yes()) {
            return true;
        }

        return self::isVendor($functionReflection);
    }

    private static function isVendor(ClassReflection|FunctionReflection $reflection): bool
    {
        $fileName = $reflection->getFileName();

        // most likely internal or magic
        if ($fileName === null) {
            return true;
        }

        // is part of vendor? we can't change that, so skip it
        return str_contains($fileName, '/vendor');
    }
}
