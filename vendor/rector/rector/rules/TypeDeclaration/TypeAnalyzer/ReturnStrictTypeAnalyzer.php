<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\FuncCall;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\StaticCall;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Scalar;
use Argtyper202511\PhpParser\Node\Scalar\Float_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ExtendedParametersAcceptor;
use Argtyper202511\PHPStan\Reflection\Native\NativeFunctionReflection;
use Argtyper202511\PHPStan\Reflection\Native\NativeMethodReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\StaticType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeTraverser;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\ParametersAcceptorSelectorVariantsWrapper;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper;
final class ReturnStrictTypeAnalyzer
{
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\TypeNodeUnwrapper
     */
    private $typeNodeUnwrapper;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(ReflectionResolver $reflectionResolver, TypeNodeUnwrapper $typeNodeUnwrapper, StaticTypeMapper $staticTypeMapper)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->typeNodeUnwrapper = $typeNodeUnwrapper;
        $this->staticTypeMapper = $staticTypeMapper;
    }
    /**
     * @param Return_[] $returns
     * @return array<Identifier|Name|NullableType>
     */
    public function collectStrictReturnTypes(array $returns, Scope $scope) : array
    {
        $containsStrictCall = \false;
        $returnedStrictTypeNodes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return [];
            }
            $returnedExpr = $return->expr;
            if ($returnedExpr instanceof MethodCall || $returnedExpr instanceof StaticCall || $returnedExpr instanceof FuncCall) {
                $containsStrictCall = \true;
                $returnNode = $this->resolveMethodCallReturnNode($returnedExpr);
            } elseif ($returnedExpr instanceof ClassConstFetch) {
                $returnNode = $this->resolveConstFetchReturnNode($returnedExpr, $scope);
            } elseif ($returnedExpr instanceof Array_ || $returnedExpr instanceof String_ || $returnedExpr instanceof Int_ || $returnedExpr instanceof Float_) {
                $returnNode = $this->resolveLiteralReturnNode($returnedExpr, $scope);
            } else {
                return [];
            }
            if (!$returnNode instanceof Node) {
                return [];
            }
            if ($returnNode instanceof Identifier && $returnNode->toString() === 'void') {
                return [];
            }
            $returnedStrictTypeNodes[] = $returnNode;
        }
        if (!$containsStrictCall) {
            return [];
        }
        return $this->typeNodeUnwrapper->uniquateNodes($returnedStrictTypeNodes);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $call
     */
    public function resolveMethodCallReturnNode($call) : ?Node
    {
        $returnType = $this->resolveMethodCallReturnType($call);
        if (!$returnType instanceof Type) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $call
     */
    public function resolveMethodCallReturnType($call) : ?Type
    {
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($call);
        if ($methodReflection === null) {
            return null;
        }
        $scope = $call->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return null;
        }
        $parametersAcceptorWithPhpDocs = ParametersAcceptorSelectorVariantsWrapper::select($methodReflection, $call, $scope);
        if ($methodReflection instanceof NativeFunctionReflection || $methodReflection instanceof NativeMethodReflection) {
            $returnType = $parametersAcceptorWithPhpDocs->getReturnType();
        } elseif ($parametersAcceptorWithPhpDocs instanceof ExtendedParametersAcceptor) {
            // native return type is needed, as docblock can be false
            $returnType = $parametersAcceptorWithPhpDocs->getNativeReturnType();
        } else {
            $returnType = $parametersAcceptorWithPhpDocs->getReturnType();
        }
        if ($returnType instanceof MixedType) {
            if ($returnType->isExplicitMixed()) {
                return $returnType;
            }
            return null;
        }
        return $this->normalizeStaticType($call, $returnType);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\FuncCall $call
     */
    private function normalizeStaticType($call, Type $type) : Type
    {
        $reflectionClass = $this->reflectionResolver->resolveClassReflection($call);
        $currentClassName = $reflectionClass instanceof ClassReflection ? $reflectionClass->getName() : null;
        return TypeTraverser::map($type, static function (Type $currentType, callable $traverseCallback) use($currentClassName) : Type {
            if ($currentType instanceof StaticType && $currentClassName !== $currentType->getClassName()) {
                return new FullyQualifiedObjectType($currentType->getClassName());
            }
            return $traverseCallback($currentType);
        });
    }
    /**
     * @param \PhpParser\Node\Expr\Array_|\PhpParser\Node\Scalar $returnedExpr
     */
    private function resolveLiteralReturnNode($returnedExpr, Scope $scope) : ?Node
    {
        $returnType = $scope->getType($returnedExpr);
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType, TypeKind::RETURN);
    }
    private function resolveConstFetchReturnNode(ClassConstFetch $classConstFetch, Scope $scope) : ?Node
    {
        $constType = $scope->getType($classConstFetch);
        if ($constType instanceof MixedType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($constType, TypeKind::RETURN);
    }
}
