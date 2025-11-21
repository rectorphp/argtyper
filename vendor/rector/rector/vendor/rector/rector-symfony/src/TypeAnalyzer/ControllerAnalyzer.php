<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\TypeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\ThisType;
use Argtyper202511\PHPStan\Type\TypeWithClassName;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\Symfony\Enum\SymfonyClass;
final class ControllerAnalyzer
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
    /**
     * @param \PhpParser\Node\Expr|\PhpParser\Node\Stmt\Class_ $node
     */
    public function isController($node) : bool
    {
        if ($node instanceof Class_) {
            return $this->isControllerClass($node);
        }
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        // might be missing in a trait
        if (!$scope instanceof Scope) {
            return \false;
        }
        $nodeType = $scope->getType($node);
        if (!$nodeType instanceof TypeWithClassName) {
            return \false;
        }
        if ($nodeType instanceof ThisType) {
            $nodeType = $nodeType->getStaticObjectType();
        }
        if (!$nodeType instanceof ObjectType) {
            return \false;
        }
        $classReflection = $nodeType->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
    public function isInsideController(Node $node) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
    private function isControllerClassReflection(ClassReflection $classReflection) : bool
    {
        if ($classReflection->is(SymfonyClass::CONTROLLER)) {
            return \true;
        }
        return $classReflection->is(SymfonyClass::ABSTRACT_CONTROLLER);
    }
    private function isControllerClass(Class_ $class) : bool
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($class);
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $this->isControllerClassReflection($classReflection);
    }
}
