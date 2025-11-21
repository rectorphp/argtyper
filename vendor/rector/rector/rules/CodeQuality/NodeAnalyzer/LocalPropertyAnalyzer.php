<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\ArrayDimFetch;
use Argtyper202511\PhpParser\Node\Expr\Assign;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\Rector\CodeQuality\TypeResolver\ArrayDimFetchTypeResolver;
use Argtyper202511\Rector\CodeQuality\ValueObject\DefinedPropertyWithType;
use Argtyper202511\Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
final class LocalPropertyAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\CodeQuality\TypeResolver\ArrayDimFetchTypeResolver
     */
    private $arrayDimFetchTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @var string
     */
    private const LARAVEL_COLLECTION_CLASS = 'Argtyper202511\\Illuminate\\Support\\Collection';
    public function __construct(SimpleCallableNodeTraverser $simpleCallableNodeTraverser, NodeNameResolver $nodeNameResolver, ArrayDimFetchTypeResolver $arrayDimFetchTypeResolver, NodeTypeResolver $nodeTypeResolver, PropertyFetchAnalyzer $propertyFetchAnalyzer, TypeFactory $typeFactory)
    {
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->arrayDimFetchTypeResolver = $arrayDimFetchTypeResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @return DefinedPropertyWithType[]
     */
    public function resolveFetchedPropertiesToTypesFromClass(Class_ $class) : array
    {
        $definedPropertiesWithTypes = [];
        foreach ($class->getMethods() as $classMethod) {
            $methodName = $this->nodeNameResolver->getName($classMethod);
            $this->simpleCallableNodeTraverser->traverseNodesWithCallable($classMethod->getStmts(), function (Node $node) use(&$definedPropertiesWithTypes, $methodName) : ?int {
                if ($this->shouldSkip($node)) {
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                if ($node instanceof Assign && ($node->var instanceof PropertyFetch || $node->var instanceof ArrayDimFetch)) {
                    $propertyFetch = $node->var;
                    $propertyName = $this->resolvePropertyName($propertyFetch instanceof ArrayDimFetch ? $propertyFetch->var : $propertyFetch);
                    if ($propertyName === null) {
                        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    }
                    if ($propertyFetch instanceof ArrayDimFetch) {
                        $propertyType = $this->arrayDimFetchTypeResolver->resolve($propertyFetch, $node);
                        $definedPropertiesWithTypes[] = new DefinedPropertyWithType($propertyName, $propertyType, $methodName);
                        return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                    }
                    $propertyType = $this->nodeTypeResolver->getType($node->expr);
                    $definedPropertiesWithTypes[] = new DefinedPropertyWithType($propertyName, $propertyType, $methodName);
                    return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
                }
                $propertyName = $this->resolvePropertyName($node);
                if ($propertyName === null) {
                    return null;
                }
                $definedPropertiesWithTypes[] = new DefinedPropertyWithType($propertyName, new MixedType(), $methodName);
                return null;
            });
        }
        return $this->normalizeToSingleType($definedPropertiesWithTypes);
    }
    private function shouldSkip(Node $node) : bool
    {
        // skip anonymous classes and inner function
        if ($node instanceof Class_ || $node instanceof Function_) {
            return \true;
        }
        // skip closure call
        if ($node instanceof MethodCall && $node->var instanceof Closure) {
            return \true;
        }
        if ($node instanceof StaticCall) {
            return $this->nodeNameResolver->isName($node->class, self::LARAVEL_COLLECTION_CLASS);
        }
        return \false;
    }
    private function resolvePropertyName(Node $node) : ?string
    {
        if (!$node instanceof PropertyFetch) {
            return null;
        }
        if (!$this->propertyFetchAnalyzer->isLocalPropertyFetch($node)) {
            return null;
        }
        if ($this->shouldSkipPropertyFetch($node)) {
            return null;
        }
        return $this->nodeNameResolver->getName($node->name);
    }
    private function shouldSkipPropertyFetch(PropertyFetch $propertyFetch) : bool
    {
        if ($this->isPartOfClosureBind($propertyFetch)) {
            return \true;
        }
        return !$propertyFetch->name instanceof Identifier;
    }
    /**
     * @param DefinedPropertyWithType[] $definedPropertiesWithTypes
     * @return DefinedPropertyWithType[]
     */
    private function normalizeToSingleType(array $definedPropertiesWithTypes) : array
    {
        $definedPropertiesWithTypesByPropertyName = [];
        foreach ($definedPropertiesWithTypes as $definedPropertyWithType) {
            $definedPropertiesWithTypesByPropertyName[$definedPropertyWithType->getName()][] = $definedPropertyWithType;
        }
        $normalizedDefinedPropertiesWithTypes = [];
        foreach ($definedPropertiesWithTypesByPropertyName as $propertyName => $definedPropertiesWithTypes) {
            if (\count($definedPropertiesWithTypes) === 1) {
                $normalizedDefinedPropertiesWithTypes[] = $definedPropertiesWithTypes[0];
                continue;
            }
            $propertyTypes = [];
            foreach ($definedPropertiesWithTypes as $definedPropertyWithType) {
                /** @var DefinedPropertyWithType $definedPropertyWithType */
                $propertyTypes[] = $definedPropertyWithType->getType();
            }
            $normalizePropertyType = $this->typeFactory->createMixedPassedOrUnionType($propertyTypes);
            $normalizedDefinedPropertiesWithTypes[] = new DefinedPropertyWithType(
                $propertyName,
                $normalizePropertyType,
                // skip as multiple places can define the same property
                null
            );
        }
        return $normalizedDefinedPropertiesWithTypes;
    }
    /**
     * Local property is actually not local one, but belongs to passed object
     * See https://ocramius.github.io/blog/accessing-private-php-class-members-without-reflection/
     */
    private function isPartOfClosureBind(PropertyFetch $propertyFetch) : bool
    {
        $scope = $propertyFetch->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        return $scope->isInClosureBind();
    }
}
