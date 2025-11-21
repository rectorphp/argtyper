<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\NodeManipulator;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\Doctrine\NodeAnalyzer\AttributeFinder;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
final class NullabilityColumnPropertyTypeResolver
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\AttributeFinder
     */
    private $attributeFinder;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var string
     */
    private const COLUMN_CLASS = 'Argtyper202511\\Doctrine\\ORM\\Mapping\\Column';
    /**
     * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/basic-mapping.html#doctrine-mapping-types
     */
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, AttributeFinder $attributeFinder, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->attributeFinder = $attributeFinder;
        $this->valueResolver = $valueResolver;
    }
    public function isNullable(Property $property) : bool
    {
        $nullableExpr = $this->attributeFinder->findAttributeByClassArgByName($property, MappingClass::COLUMN, 'nullable');
        if ($nullableExpr instanceof Expr) {
            return $this->valueResolver->isTrue($nullableExpr);
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        return $this->isNullableColumn($phpDocInfo);
    }
    private function isNullableColumn(PhpDocInfo $phpDocInfo) : bool
    {
        $doctrineAnnotationTagValueNode = $phpDocInfo->findOneByAnnotationClass(self::COLUMN_CLASS);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return \true;
        }
        $nullableValueArrayItemNode = $doctrineAnnotationTagValueNode->getValue('nullable');
        if (!$nullableValueArrayItemNode instanceof ArrayItemNode) {
            return \true;
        }
        return $nullableValueArrayItemNode->value instanceof ConstExprTrueNode;
    }
}
