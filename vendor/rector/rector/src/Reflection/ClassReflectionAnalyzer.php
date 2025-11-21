<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Reflection;

use Argtyper202511\PHPStan\Reflection\ClassReflection;
use ReflectionEnum;
final class ClassReflectionAnalyzer
{
    public function resolveParentClassName(ClassReflection $classReflection) : ?string
    {
        $nativeReflection = $classReflection->getNativeReflection();
        if ($nativeReflection instanceof ReflectionEnum) {
            return null;
        }
        return $nativeReflection->getParentClassName();
    }
}
