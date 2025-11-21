<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Symfony\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\Rector\Naming\Naming\PropertyNaming;
use Argtyper202511\Rector\NodeManipulator\PropertyManipulator;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\Php80\NodeAnalyzer\PromotedPropertyResolver;
use Argtyper202511\Rector\PostRector\ValueObject\PropertyMetadata;
final class DependencyInjectionMethodCallAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\PropertyNaming
     */
    private $propertyNaming;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver
     */
    private $serviceTypeMethodCallResolver;
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PromotedPropertyResolver
     */
    private $promotedPropertyResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeManipulator\PropertyManipulator
     */
    private $propertyManipulator;
    public function __construct(PropertyNaming $propertyNaming, \Argtyper202511\Rector\Symfony\NodeAnalyzer\ServiceTypeMethodCallResolver $serviceTypeMethodCallResolver, PromotedPropertyResolver $promotedPropertyResolver, NodeNameResolver $nodeNameResolver, PropertyManipulator $propertyManipulator)
    {
        $this->propertyNaming = $propertyNaming;
        $this->serviceTypeMethodCallResolver = $serviceTypeMethodCallResolver;
        $this->promotedPropertyResolver = $promotedPropertyResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->propertyManipulator = $propertyManipulator;
    }
    public function replaceMethodCallWithPropertyFetchAndDependency(Class_ $class, MethodCall $methodCall): ?PropertyMetadata
    {
        $serviceType = $this->serviceTypeMethodCallResolver->resolve($methodCall);
        if (!$serviceType instanceof ObjectType) {
            return null;
        }
        $resolvedPropertyNameByType = $this->propertyManipulator->resolveExistingClassPropertyNameByType($class, $serviceType);
        if (is_string($resolvedPropertyNameByType)) {
            $propertyName = $resolvedPropertyNameByType;
        } else {
            $propertyName = $this->propertyNaming->fqnToVariableName($serviceType);
            $propertyName = $this->resolveNewPropertyNameWhenExists($class, $propertyName, $propertyName);
        }
        return new PropertyMetadata($propertyName, $serviceType);
    }
    private function resolveNewPropertyNameWhenExists(Class_ $class, string $originalPropertyName, string $propertyName, int $count = 1): string
    {
        $lastCount = (string) substr($propertyName, strlen($originalPropertyName));
        if (is_numeric($lastCount)) {
            $count = (int) $lastCount;
        }
        $promotedPropertyParams = $this->promotedPropertyResolver->resolveFromClass($class);
        foreach ($promotedPropertyParams as $promotedPropertyParam) {
            if ($this->nodeNameResolver->isName($promotedPropertyParam->var, $propertyName)) {
                $propertyName = $this->resolveIncrementPropertyName($originalPropertyName, $count);
                return $this->resolveNewPropertyNameWhenExists($class, $originalPropertyName, $propertyName, $count);
            }
        }
        $property = $class->getProperty($propertyName);
        if (!$property instanceof Property) {
            return $propertyName;
        }
        $propertyName = $this->resolveIncrementPropertyName($originalPropertyName, $count);
        return $this->resolveNewPropertyNameWhenExists($class, $originalPropertyName, $propertyName, $count);
    }
    private function resolveIncrementPropertyName(string $originalPropertyName, int $count): string
    {
        ++$count;
        return $originalPropertyName . $count;
    }
}
