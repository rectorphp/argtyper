<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeAnalyzer;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\PropertyItem;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
final class PropertyTypeDefaultValueAnalyzer
{
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    public function __construct(StaticTypeMapper $staticTypeMapper)
    {
        $this->staticTypeMapper = $staticTypeMapper;
    }
    public function doesConflictWithDefaultValue(PropertyItem $propertyItem, Type $propertyType): bool
    {
        if (!$propertyItem->default instanceof Expr) {
            return \false;
        }
        // the defaults can be in conflict
        $defaultType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($propertyItem->default);
        if ($defaultType->isArray()->yes() && $propertyType->isArray()->yes()) {
            return \false;
        }
        // type is not matching, skip it
        return !$defaultType->isSuperTypeOf($propertyType)->yes();
    }
}
