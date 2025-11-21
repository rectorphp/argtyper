<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Arguments\NodeAnalyzer;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\TypeComparator\TypeComparator;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class ChangedArgumentsDetector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\TypeComparator\TypeComparator
     */
    private $typeComparator;
    public function __construct(ValueResolver $valueResolver, StaticTypeMapper $staticTypeMapper, TypeComparator $typeComparator)
    {
        $this->valueResolver = $valueResolver;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->typeComparator = $typeComparator;
    }
    /**
     * @param mixed $value
     */
    public function isDefaultValueChanged(Param $param, $value): bool
    {
        if (!$param->default instanceof Expr) {
            return \false;
        }
        return !$this->valueResolver->isValue($param->default, $value);
    }
    public function isTypeChanged(Param $param, ?Type $newType): bool
    {
        if (!$param->type instanceof Node) {
            return \false;
        }
        if (!$newType instanceof Type) {
            return \true;
        }
        $currentParamType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($param->type);
        return !$this->typeComparator->areTypesEqual($currentParamType, $newType);
    }
}
