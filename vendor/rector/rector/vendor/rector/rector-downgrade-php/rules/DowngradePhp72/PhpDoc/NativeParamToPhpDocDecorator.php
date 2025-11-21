<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp72\PhpDoc;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Argtyper202511\Rector\NodeNameResolver\NodeNameResolver;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class NativeParamToPhpDocDecorator
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger
     */
    private $phpDocTypeChanger;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, NodeNameResolver $nodeNameResolver, StaticTypeMapper $staticTypeMapper, PhpDocTypeChanger $phpDocTypeChanger, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocTypeChanger = $phpDocTypeChanger;
        $this->valueResolver = $valueResolver;
    }
    public function decorate(ClassMethod $classMethod, Param $param) : void
    {
        if (!$param->type instanceof Node) {
            return;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
        $paramName = $this->nodeNameResolver->getName($param);
        $mappedCurrentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        $correctedNullableParamType = $this->correctNullableType($param, $mappedCurrentParamType);
        $this->phpDocTypeChanger->changeParamType($classMethod, $phpDocInfo, $correctedNullableParamType, $param, $paramName);
    }
    private function isParamNullable(Param $param) : bool
    {
        if (!$param->default instanceof Expr) {
            return \false;
        }
        return $this->valueResolver->isNull($param->default);
    }
    /**
     * @return \PHPStan\Type\UnionType|\PHPStan\Type\Type
     */
    private function correctNullableType(Param $param, Type $paramType)
    {
        if (!$this->isParamNullable($param)) {
            return $paramType;
        }
        if (TypeCombinator::containsNull($paramType)) {
            return $paramType;
        }
        // add default null type
        return TypeCombinator::addNull($paramType);
    }
}
