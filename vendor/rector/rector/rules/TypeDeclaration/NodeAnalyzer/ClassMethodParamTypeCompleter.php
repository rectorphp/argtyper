<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver;
final class ClassMethodParamTypeCompleter
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodParamVendorLockResolver
     */
    private $classMethodParamVendorLockResolver;
    public function __construct(StaticTypeMapper $staticTypeMapper, ClassMethodParamVendorLockResolver $classMethodParamVendorLockResolver)
    {
        $this->staticTypeMapper = $staticTypeMapper;
        $this->classMethodParamVendorLockResolver = $classMethodParamVendorLockResolver;
    }
    /**
     * @param array<int, Type> $classParameterTypes
     */
    public function complete(ClassMethod $classMethod, array $classParameterTypes, int $maxUnionTypes): ?ClassMethod
    {
        $hasChanged = \false;
        foreach ($classParameterTypes as $position => $argumentStaticType) {
            /** @var Type $argumentStaticType */
            if ($this->shouldSkipArgumentStaticType($classMethod, $argumentStaticType, $position, $maxUnionTypes)) {
                continue;
            }
            $phpParserTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($argumentStaticType, TypeKind::PARAM);
            if (!$phpParserTypeNode instanceof Node) {
                continue;
            }
            // check default override
            $param = $classMethod->params[$position];
            if (!$this->isAcceptedByDefault($param, $argumentStaticType)) {
                continue;
            }
            // skip if param type already filled
            if ($param->type instanceof Node) {
                continue;
            }
            // update parameter
            $param->type = $phpParserTypeNode;
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $classMethod;
        }
        return null;
    }
    private function shouldSkipArgumentStaticType(ClassMethod $classMethod, Type $argumentStaticType, int $position, int $maxUnionTypes): bool
    {
        if ($argumentStaticType instanceof MixedType) {
            return \true;
        }
        // skip mixed in union type
        if ($argumentStaticType instanceof UnionType && $argumentStaticType->isSuperTypeOf(new MixedType())->yes()) {
            return \true;
        }
        if (!isset($classMethod->params[$position])) {
            return \true;
        }
        if ($this->classMethodParamVendorLockResolver->isVendorLocked($classMethod)) {
            return \true;
        }
        $parameter = $classMethod->params[$position];
        if ($parameter->type === null) {
            return \false;
        }
        $currentParameterStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($parameter->type);
        if ($this->isClosureAndCallableType($currentParameterStaticType, $argumentStaticType)) {
            return \true;
        }
        // too many union types
        if ($this->isTooDetailedUnionType($currentParameterStaticType, $argumentStaticType, $maxUnionTypes)) {
            return \true;
        }
        // current type already accepts the one added
        if ($currentParameterStaticType->accepts($argumentStaticType, \true)->yes()) {
            return \true;
        }
        // avoid overriding more precise type
        if ($argumentStaticType->isSuperTypeOf($currentParameterStaticType)->yes()) {
            return \true;
        }
        // already completed → skip
        return $currentParameterStaticType->equals($argumentStaticType);
    }
    private function isClosureAndCallableType(Type $parameterStaticType, Type $argumentStaticType): bool
    {
        if ($parameterStaticType->isCallable()->yes() && $this->isClosureObjectType($argumentStaticType)) {
            return \true;
        }
        return $argumentStaticType->isCallable()->yes() && $this->isClosureObjectType($parameterStaticType);
    }
    private function isClosureObjectType(Type $type): bool
    {
        if (!$type instanceof ObjectType) {
            return \false;
        }
        return $type->getClassName() === 'Closure';
    }
    private function isTooDetailedUnionType(Type $currentType, Type $newType, int $maxUnionTypes): bool
    {
        if ($currentType instanceof MixedType) {
            return \false;
        }
        if (!$newType instanceof UnionType) {
            return \false;
        }
        return count($newType->getTypes()) > $maxUnionTypes;
    }
    private function isAcceptedByDefault(Param $param, Type $argumentStaticType): bool
    {
        if (!$param->default instanceof Expr) {
            return \true;
        }
        $defaultExpr = $param->default;
        $defaultStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($defaultExpr);
        return $argumentStaticType->accepts($defaultStaticType, \false)->yes();
    }
}
