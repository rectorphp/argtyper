<?php

declare(strict_types=1);

namespace Rector\ArgTyper\Helpers;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\TrinaryLogic;

final class ReflectionChecker
{
    public static function shouldSkip(ClassReflection|FunctionReflection $reflection): bool
    {
        if ($reflection->isInternal() instanceof TrinaryLogic) {
            if ($reflection->isInternal()->yes()) {
                return true;
            }
        } elseif ($reflection->isInternal()) {
            return true;
        }

        $fileName = $reflection->getFileName();

        // most likely internal or magic
        if ($fileName === null) {
            return true;
        }

        return str_contains($fileName, '/vendor');
    }
}
