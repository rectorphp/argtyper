<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php82\NodeManipulator;

use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\BetterReflection\Reflection\Adapter\ReflectionProperty;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Argtyper202511\Rector\Php81\Enum\AttributeName;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Argtyper202511\Rector\ValueObject\MethodName;
use Argtyper202511\Rector\ValueObject\Visibility;
final class ReadonlyClassManipulator
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(VisibilityManipulator $visibilityManipulator, PhpAttributeAnalyzer $phpAttributeAnalyzer, ReflectionProvider $reflectionProvider)
    {
        $this->visibilityManipulator = $visibilityManipulator;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function process(Class_ $class) : ?\Argtyper202511\PhpParser\Node\Stmt\Class_
    {
        $scope = ScopeFetcher::fetch($class);
        if ($this->shouldSkip($class, $scope)) {
            return null;
        }
        $this->visibilityManipulator->makeReadonly($class);
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if ($constructClassMethod instanceof ClassMethod) {
            foreach ($constructClassMethod->getParams() as $param) {
                $this->visibilityManipulator->removeReadonly($param);
            }
        }
        foreach ($class->getProperties() as $property) {
            $this->visibilityManipulator->removeReadonly($property);
        }
        return $class;
    }
    /**
     * @return ClassReflection[]
     */
    private function resolveParentClassReflections(Scope $scope) : array
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return [];
        }
        return $classReflection->getParents();
    }
    /**
     * @param Property[] $properties
     */
    private function hasNonTypedProperty(array $properties) : bool
    {
        foreach ($properties as $property) {
            // properties of readonly class must always have type
            if ($property->type === null) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkip(Class_ $class, Scope $scope) : bool
    {
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        if ($this->shouldSkipClass($class)) {
            return \true;
        }
        $parents = $this->resolveParentClassReflections($scope);
        if (!$class->isAnonymous() && !$class->isFinal()) {
            return !$this->isExtendsReadonlyClass($parents);
        }
        foreach ($parents as $parent) {
            if (!$parent->isReadOnly()) {
                return \true;
            }
        }
        $properties = $class->getProperties();
        if ($this->hasWritableProperty($properties)) {
            return \true;
        }
        if ($this->hasNonTypedProperty($properties)) {
            return \true;
        }
        if ($this->shouldSkipConsumeTraitProperty($class)) {
            return \true;
        }
        $constructClassMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$constructClassMethod instanceof ClassMethod) {
            // no __construct means no property promotion, skip if class has no property defined
            return $properties === [];
        }
        $params = $constructClassMethod->getParams();
        if ($params === []) {
            // no params means no property promotion, skip if class has no property defined
            return $properties === [];
        }
        return $this->shouldSkipParams($params);
    }
    private function shouldSkipConsumeTraitProperty(Class_ $class) : bool
    {
        $traitUses = $class->getTraitUses();
        foreach ($traitUses as $traitUse) {
            foreach ($traitUse->traits as $trait) {
                $traitName = $trait->toString();
                // trait not autoloaded
                if (!$this->reflectionProvider->hasClass($traitName)) {
                    return \true;
                }
                $traitClassReflection = $this->reflectionProvider->getClass($traitName);
                $nativeReflection = $traitClassReflection->getNativeReflection();
                if ($this->hasReadonlyProperty($nativeReflection->getProperties())) {
                    return \true;
                }
            }
        }
        return \false;
    }
    /**
     * @param ReflectionProperty[] $properties
     */
    private function hasReadonlyProperty(array $properties) : bool
    {
        foreach ($properties as $property) {
            if (!$property->isReadOnly()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param ClassReflection[] $parents
     */
    private function isExtendsReadonlyClass(array $parents) : bool
    {
        foreach ($parents as $parent) {
            if ($parent->isReadOnly()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param Property[] $properties
     */
    private function hasWritableProperty(array $properties) : bool
    {
        foreach ($properties as $property) {
            if (!$property->isReadonly()) {
                return \true;
            }
        }
        return \false;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // need to have test fixture once feature added to  nikic/PHP-Parser
        if ($this->visibilityManipulator->hasVisibility($class, Visibility::READONLY)) {
            return \true;
        }
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($class, AttributeName::ALLOW_DYNAMIC_PROPERTIES)) {
            return \true;
        }
        return $class->extends instanceof FullyQualified && !$this->reflectionProvider->hasClass($class->extends->toString());
    }
    /**
     * @param Param[] $params
     */
    private function shouldSkipParams(array $params) : bool
    {
        foreach ($params as $param) {
            // has non-readonly property promotion
            if (!$this->visibilityManipulator->hasVisibility($param, Visibility::READONLY) && $param->isPromoted()) {
                return \true;
            }
            // type is missing, invalid syntax
            if ($param->type === null) {
                return \true;
            }
        }
        return \false;
    }
}
