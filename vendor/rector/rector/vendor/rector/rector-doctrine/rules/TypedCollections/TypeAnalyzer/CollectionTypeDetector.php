<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\TypeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PHPStan\Type\IntersectionType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class CollectionTypeDetector
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isCollectionNonNullableType(Expr $expr): bool
    {
        $exprType = $this->nodeTypeResolver->getType($expr);
        return $this->isCollectionObjectType($exprType);
    }
    public function isCollectionType(Expr $expr): bool
    {
        $exprType = $this->nodeTypeResolver->getType($expr);
        if ($exprType instanceof IntersectionType) {
            foreach ($exprType->getTypes() as $intersectionedType) {
                if ($this->isCollectionObjectType($intersectionedType)) {
                    return \true;
                }
            }
        }
        if ($exprType instanceof UnionType) {
            $bareExprType = TypeCombinator::removeNull($exprType);
            return $this->isCollectionObjectType($bareExprType);
        }
        return $this->isCollectionObjectType($exprType);
    }
    private function isCollectionObjectType(Type $exprType): bool
    {
        if ($exprType instanceof IntersectionType) {
            foreach ($exprType->getTypes() as $intersectionedType) {
                if ($this->isCollectionObjectType($intersectionedType)) {
                    return \true;
                }
            }
        }
        if (!$exprType instanceof ObjectType) {
            return \false;
        }
        return $exprType->isInstanceOf(DoctrineClass::COLLECTION)->yes();
    }
}
