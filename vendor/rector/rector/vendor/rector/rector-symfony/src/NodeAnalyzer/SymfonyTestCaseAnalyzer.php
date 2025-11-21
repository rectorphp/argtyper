<?php

declare (strict_types=1);
namespace Rector\Symfony\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Rector\Reflection\ReflectionResolver;
final class SymfonyTestCaseAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function isInWebTestCase(Node $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->is('Argtyper202511\Symfony\Bundle\FrameworkBundle\Test\WebTestCase');
    }
    /**
     * @api
     */
    public function isInKernelTestCase(Node $node): bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->is('Argtyper202511\Symfony\Bundle\FrameworkBundle\Test\KernelTestCase');
    }
}
