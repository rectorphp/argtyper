<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Reflection;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
final class ClassModifierChecker
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Argtyper202511\Rector\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isInsideFinalClass(Node $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isFinalByKeyword();
    }
    public function isInsideAbstractClass(Node $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->isAbstract();
    }
}
