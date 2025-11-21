<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp72\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
final class BuiltInMethodAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isImplementsBuiltInInterface(ClassReflection $classReflection, ClassMethod $classMethod) : bool
    {
        if (!$classReflection->isClass()) {
            return \false;
        }
        $methodName = $this->nodeNameResolver->getName($classMethod);
        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if (!$interfaceReflection->isBuiltin()) {
                continue;
            }
            if (!$interfaceReflection->hasMethod($methodName)) {
                continue;
            }
            return \true;
        }
        return \false;
    }
}
