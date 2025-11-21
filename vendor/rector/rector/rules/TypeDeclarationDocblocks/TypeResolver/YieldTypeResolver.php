<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclarationDocblocks\TypeResolver;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Closure;
use Argtyper202511\PhpParser\Node\Expr\Yield_;
use Argtyper202511\PhpParser\Node\Expr\YieldFrom;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
final class YieldTypeResolver
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver, TypeFactory $typeFactory)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->typeFactory = $typeFactory;
    }
    /**
     * @param array<Yield_|YieldFrom> $yieldNodes
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Function_ $functionLike
     * @return \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType|\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedGenericObjectType
     */
    public function resolveFromYieldNodes(array $yieldNodes, $functionLike)
    {
        $yieldedTypes = $this->resolveYieldedTypes($yieldNodes);
        $className = $this->resolveClassName($functionLike);
        if ($yieldedTypes === []) {
            return new FullyQualifiedObjectType($className);
        }
        $yieldedTypes = $this->typeFactory->createMixedPassedOrUnionType($yieldedTypes);
        return new FullyQualifiedGenericObjectType($className, [$yieldedTypes]);
    }
    /**
     * @param \PhpParser\Node\Expr\Yield_|\PhpParser\Node\Expr\YieldFrom $yield
     */
    private function resolveYieldValue($yield): ?Expr
    {
        if ($yield instanceof Yield_) {
            return $yield->value;
        }
        return $yield->expr;
    }
    /**
     * @param array<Yield_|YieldFrom> $yieldNodes
     * @return Type[]
     */
    private function resolveYieldedTypes(array $yieldNodes): array
    {
        $yieldedTypes = [];
        foreach ($yieldNodes as $yieldNode) {
            $value = $this->resolveYieldValue($yieldNode);
            if (!$value instanceof Expr) {
                // one of the yields is empty
                return [];
            }
            $resolvedType = $this->nodeTypeResolver->getType($value);
            if ($resolvedType instanceof MixedType) {
                continue;
            }
            $yieldedTypes[] = $resolvedType;
        }
        return $yieldedTypes;
    }
    /**
     * @param \PhpParser\Node\Stmt\Function_|\PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Expr\Closure $functionLike
     */
    private function resolveClassName($functionLike): string
    {
        $returnTypeNode = $functionLike->getReturnType();
        if ($returnTypeNode instanceof Identifier && $returnTypeNode->name === 'iterable') {
            return 'Iterator';
        }
        if ($returnTypeNode instanceof Name && !$this->nodeNameResolver->isName($returnTypeNode, 'Generator')) {
            return $this->nodeNameResolver->getName($returnTypeNode);
        }
        return 'Generator';
    }
}
