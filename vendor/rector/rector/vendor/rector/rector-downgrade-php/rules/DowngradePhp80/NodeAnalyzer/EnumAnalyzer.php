<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\NodeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Stmt\Enum_;
use Argtyper202511\PhpParser\Node\Stmt\EnumCase;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\PhpParser\AstResolver;
final class EnumAnalyzer
{
    /**
     * @readonly
     * @var \Rector\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(AstResolver $astResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->astResolver = $astResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function resolveType(ClassReflection $classReflection) : ?Identifier
    {
        $class = $this->astResolver->resolveClassFromClassReflection($classReflection);
        if (!$class instanceof Enum_) {
            return null;
        }
        $scalarType = $class->scalarType;
        if ($scalarType instanceof Identifier) {
            // can be only int or string
            return $scalarType;
        }
        $enumExprTypes = $this->resolveEnumExprTypes($class);
        $enumExprTypeClasses = [];
        foreach ($enumExprTypes as $enumExprType) {
            $enumExprTypeClasses[] = \get_class($enumExprType);
        }
        $uniqueEnumExprTypeClasses = \array_unique($enumExprTypeClasses);
        if (\count($uniqueEnumExprTypeClasses) === 1) {
            $uniqueEnumExprTypeClass = $uniqueEnumExprTypeClasses[0];
            if (\is_a($uniqueEnumExprTypeClass, StringType::class, \true)) {
                return new Identifier('string');
            }
            if (\is_a($uniqueEnumExprTypeClass, IntegerType::class, \true)) {
                return new Identifier('int');
            }
            if (\is_a($uniqueEnumExprTypeClass, FloatType::class, \true)) {
                return new Identifier('float');
            }
        }
        // unknown or multiple types
        return null;
    }
    /**
     * @return Type[]
     */
    private function resolveEnumExprTypes(Enum_ $enum) : array
    {
        $enumExprTypes = [];
        foreach ($enum->stmts as $classStmt) {
            if (!$classStmt instanceof EnumCase) {
                continue;
            }
            $enumExprTypes[] = $this->resolveEnumCaseType($classStmt);
        }
        return $enumExprTypes;
    }
    private function resolveEnumCaseType(EnumCase $enumCase) : Type
    {
        $classExpr = $enumCase->expr;
        if ($classExpr instanceof Expr) {
            return $this->nodeTypeResolver->getType($classExpr);
        }
        // in case of no value, fallback to string type
        return new StringType();
    }
}
