<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeInferer\ReturnTypeInferer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\VoidType;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\PhpParser\Node\BetterNodeFinder;
use Argtyper202511\Rector\Reflection\ReflectionResolver;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\SilentVoidResolver;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower;
/**
 * @internal
 */
final class ReturnedNodesReturnTypeInfererTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SilentVoidResolver
     */
    private $silentVoidResolver;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\SplArrayFixedTypeNarrower
     */
    private $splArrayFixedTypeNarrower;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(SilentVoidResolver $silentVoidResolver, BetterNodeFinder $betterNodeFinder, NodeTypeResolver $nodeTypeResolver, TypeFactory $typeFactory, SplArrayFixedTypeNarrower $splArrayFixedTypeNarrower, ReflectionResolver $reflectionResolver)
    {
        $this->silentVoidResolver = $silentVoidResolver;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->typeFactory = $typeFactory;
        $this->splArrayFixedTypeNarrower = $splArrayFixedTypeNarrower;
        $this->reflectionResolver = $reflectionResolver;
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_|\PhpParser\Node\Expr\Closure $functionLike
     */
    public function inferFunctionLike($functionLike) : Type
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($functionLike);
        if ($functionLike instanceof ClassMethod && (!$classReflection instanceof ClassReflection || $classReflection->isInterface())) {
            return new MixedType();
        }
        $types = [];
        // empty returns can have yield, use MixedType() instead
        $localReturnNodes = $this->betterNodeFinder->findReturnsScoped($functionLike);
        if ($localReturnNodes === []) {
            return new MixedType();
        }
        $hasVoid = \false;
        foreach ($localReturnNodes as $localReturnNode) {
            if (!$localReturnNode->expr instanceof Expr) {
                $hasVoid = \true;
                $types[] = new VoidType();
                continue;
            }
            $returnedExprType = $this->nodeTypeResolver->getNativeType($localReturnNode->expr);
            $types[] = $this->splArrayFixedTypeNarrower->narrow($returnedExprType);
        }
        if (!$hasVoid && $this->silentVoidResolver->hasSilentVoid($functionLike)) {
            $types[] = new VoidType();
        }
        $returnType = $this->typeFactory->createMixedPassedOrUnionTypeAndKeepConstant($types);
        // only void?
        if ($returnType->isVoid()->yes()) {
            return new MixedType();
        }
        return $returnType;
    }
}
