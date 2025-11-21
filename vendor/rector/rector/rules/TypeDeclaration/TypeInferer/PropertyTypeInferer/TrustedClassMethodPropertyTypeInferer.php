<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\NodeAnalyzer\ParamAnalyzer;
use Argtyper202511\Rector\NodeManipulator\ClassMethodPropertyFetchManipulator;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Argtyper202511\Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
/**
 * @internal
 */
final class TrustedClassMethodPropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\ClassMethodPropertyFetchManipulator
     */
    private $classMethodPropertyFetchManipulator;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\PhpDocParser\NodeTraverser\SimpleCallableNodeTraverser
     */
    private $simpleCallableNodeTraverser;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeAnalyzer\ParamAnalyzer
     */
    private $paramAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(ClassMethodPropertyFetchManipulator $classMethodPropertyFetchManipulator, ReflectionProvider $reflectionProvider, NodeNameResolver $nodeNameResolver, SimpleCallableNodeTraverser $simpleCallableNodeTraverser, TypeFactory $typeFactory, StaticTypeMapper $staticTypeMapper, NodeTypeResolver $nodeTypeResolver, ParamAnalyzer $paramAnalyzer, AssignToPropertyTypeInferer $assignToPropertyTypeInferer, TypeComparator $typeComparator)
    {
        $this->classMethodPropertyFetchManipulator = $classMethodPropertyFetchManipulator;
        $this->reflectionProvider = $reflectionProvider;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->simpleCallableNodeTraverser = $simpleCallableNodeTraverser;
        $this->typeFactory = $typeFactory;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->paramAnalyzer = $paramAnalyzer;
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->typeComparator = $typeComparator;
    }
    public function inferProperty(Class_ $class, Property $property, ClassMethod $classMethod): Type
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        // 1. direct property = param assign
        $param = $this->classMethodPropertyFetchManipulator->findParamAssignToPropertyName($classMethod, $propertyName);
        if ($param instanceof Param) {
            return $this->resolveTypeFromParam($param, $classMethod, $propertyName, $property, $class);
        }
        // 2. different assign
        /** @var Expr[] $assignedExprs */
        $assignedExprs = $this->classMethodPropertyFetchManipulator->findAssignsToPropertyName($classMethod, $propertyName);
        $resolvedTypes = [];
        foreach ($assignedExprs as $assignedExpr) {
            $resolvedTypes[] = $this->nodeTypeResolver->getNativeType($assignedExpr);
        }
        if ($resolvedTypes === []) {
            return new MixedType();
        }
        $resolvedType = count($resolvedTypes) === 1 ? $resolvedTypes[0] : TypeCombinator::union(...$resolvedTypes);
        return $this->resolveType($property, $propertyName, $class, $resolvedType);
    }
    private function resolveType(Property $property, string $propertyName, Class_ $class, Type $resolvedType): Type
    {
        $exactType = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $class);
        if (!$exactType instanceof UnionType) {
            return $resolvedType;
        }
        if ($this->typeComparator->areTypesEqual($resolvedType, $exactType)) {
            return $resolvedType;
        }
        return new MixedType();
    }
    private function resolveFromParamType(Param $param, ClassMethod $classMethod, string $propertyName): Type
    {
        $type = $this->resolveParamTypeToPHPStanType($param);
        if ($type instanceof MixedType) {
            return new MixedType();
        }
        $types = [];
        // it's an array - annotation → make type more precise, if possible
        if ($type->isArray()->yes() || $param->variadic) {
            $types[] = $this->getResolveParamStaticTypeAsPHPStanType($classMethod, $propertyName);
        } else {
            $types[] = $type;
        }
        if ($this->isParamNullable($param)) {
            $types[] = new NullType();
        }
        return $this->typeFactory->createMixedPassedOrUnionType($types);
    }
    private function resolveParamTypeToPHPStanType(Param $param): Type
    {
        if (!$param->type instanceof Node) {
            return new MixedType();
        }
        if ($this->paramAnalyzer->isNullable($param)) {
            /** @var NullableType $type */
            $type = $param->type;
            $types = [];
            $types[] = new NullType();
            $types[] = $this->staticTypeMapper->mapPhpParserNodePHPStanType($type->type);
            return $this->typeFactory->createMixedPassedOrUnionType($types);
        }
        // special case for alias
        if ($param->type instanceof FullyQualified) {
            $type = $this->resolveFullyQualifiedOrAliasedObjectType($param);
            if ($type instanceof Type) {
                return $type;
            }
        }
        return $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
    }
    private function getResolveParamStaticTypeAsPHPStanType(ClassMethod $classMethod, string $propertyName): Type
    {
        $paramStaticType = new ArrayType(new MixedType(), new MixedType());
        $this->simpleCallableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use ($propertyName, &$paramStaticType): ?int {
            if (!$node instanceof Variable) {
                return null;
            }
            if (!$this->nodeNameResolver->isName($node, $propertyName)) {
                return null;
            }
            $paramStaticType = $this->nodeTypeResolver->getType($node);
            return NodeVisitor::STOP_TRAVERSAL;
        });
        return $paramStaticType;
    }
    private function isParamNullable(Param $param): bool
    {
        if ($this->paramAnalyzer->isNullable($param)) {
            return \true;
        }
        if ($param->default instanceof Expr) {
            $defaultValueStaticType = $this->nodeTypeResolver->getType($param->default);
            if ($defaultValueStaticType->isNull()->yes()) {
                return \true;
            }
        }
        return \false;
    }
    private function resolveFullyQualifiedOrAliasedObjectType(Param $param): ?Type
    {
        if (!$param->type instanceof Node) {
            return null;
        }
        $fullyQualifiedName = $this->nodeNameResolver->getName($param->type);
        if (!is_string($fullyQualifiedName)) {
            return null;
        }
        $originalName = $param->type->getAttribute(AttributeKey::ORIGINAL_NAME);
        if (!$originalName instanceof Name) {
            return null;
        }
        // if the FQN has different ending than the original, it was aliased and we need to return the alias
        if (substr_compare($fullyQualifiedName, '\\' . $originalName->toString(), -strlen('\\' . $originalName->toString())) !== 0) {
            $className = $originalName->toString();
            if ($this->reflectionProvider->hasClass($className)) {
                return new FullyQualifiedObjectType($className);
            }
            // @note: $fullyQualifiedName is a guess, needs real life test
            return new AliasedObjectType($originalName->toString(), $fullyQualifiedName);
        }
        return null;
    }
    private function resolveTypeFromParam(Param $param, ClassMethod $classMethod, string $propertyName, Property $property, Class_ $class): Type
    {
        if (!$param->type instanceof Node) {
            return new MixedType();
        }
        $resolvedType = $this->resolveFromParamType($param, $classMethod, $propertyName);
        return $this->resolveType($property, $propertyName, $class, $resolvedType);
    }
}
