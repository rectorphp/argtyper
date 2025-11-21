<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer;

use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\TypeDeclaration\FunctionLikeReturnTypeResolver;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer;
final class GetterTypeDeclarationPropertyTypeInferer
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\FunctionLikeReturnTypeResolver
     */
    private $functionLikeReturnTypeResolver;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\ClassMethodAndPropertyAnalyzer
     */
    private $classMethodAndPropertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(FunctionLikeReturnTypeResolver $functionLikeReturnTypeResolver, ClassMethodAndPropertyAnalyzer $classMethodAndPropertyAnalyzer, NodeNameResolver $nodeNameResolver)
    {
        $this->functionLikeReturnTypeResolver = $functionLikeReturnTypeResolver;
        $this->classMethodAndPropertyAnalyzer = $classMethodAndPropertyAnalyzer;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function inferProperty(Property $property, Class_ $class): ?Type
    {
        /** @var string $propertyName */
        $propertyName = $this->nodeNameResolver->getName($property);
        foreach ($class->getMethods() as $classMethod) {
            if (!$this->classMethodAndPropertyAnalyzer->hasPropertyFetchReturn($classMethod, $propertyName)) {
                continue;
            }
            $returnType = $this->functionLikeReturnTypeResolver->resolveFunctionLikeReturnTypeToPHPStanType($classMethod);
            // let PhpDoc solve that later for more precise type
            if ($returnType->isArray()->yes()) {
                return new MixedType();
            }
            if (!$returnType instanceof MixedType) {
                return $returnType;
            }
        }
        return null;
    }
}
