<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Doctrine\CodeQuality\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Expr\UnaryMinus;
use Argtyper202511\PhpParser\Node\Expr\UnaryPlus;
use Argtyper202511\PhpParser\Node\PropertyItem;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Doctrine\Enum\MappingClass;
use Argtyper202511\Rector\Exception\NotImplementedYetException;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\CorrectDefaultTypesOnEntityPropertyRectorTest
 */
final class CorrectDefaultTypesOnEntityPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ValueResolver $valueResolver)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change default value types to match Doctrine annotation type', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = '0';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = false;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass(MappingClass::COLUMN);
        if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $onlyProperty = $node->props[0];
        $defaultValue = $onlyProperty->default;
        if (!$defaultValue instanceof Expr) {
            return null;
        }
        $typeArrayItemNode = $doctrineAnnotationTagValueNode->getValue('type');
        if (!$typeArrayItemNode instanceof ArrayItemNode) {
            return null;
        }
        $typeValue = $typeArrayItemNode->value;
        if ($typeValue instanceof StringNode) {
            $typeValue = $typeValue->value;
        }
        if (!is_string($typeValue)) {
            return null;
        }
        if (in_array($typeValue, ['bool', 'boolean'], \true)) {
            return $this->refactorToBoolType($onlyProperty, $node);
        }
        if (in_array($typeValue, ['int', 'integer', 'bigint', 'smallint'], \true)) {
            return $this->refactorToIntType($onlyProperty, $node);
        }
        return null;
    }
    private function refactorToBoolType(PropertyItem $propertyItem, Property $property): ?Property
    {
        if (!$propertyItem->default instanceof Expr) {
            return null;
        }
        $defaultExpr = $propertyItem->default;
        if ($defaultExpr instanceof String_) {
            $propertyItem->default = (bool) $defaultExpr->value ? $this->nodeFactory->createTrue() : $this->nodeFactory->createFalse();
            return $property;
        }
        if ($defaultExpr instanceof ConstFetch || $defaultExpr instanceof ClassConstFetch) {
            // already ok
            return null;
        }
        throw new NotImplementedYetException();
    }
    private function refactorToIntType(PropertyItem $propertyItem, Property $property): ?Property
    {
        if (!$propertyItem->default instanceof Expr) {
            return null;
        }
        $defaultExpr = $propertyItem->default;
        if ($defaultExpr instanceof String_) {
            $propertyItem->default = new Int_((int) $defaultExpr->value);
            return $property;
        }
        if ($defaultExpr instanceof Int_) {
            // already correct
            return null;
        }
        // default value on nullable property
        if ($this->valueResolver->isNull($defaultExpr)) {
            return null;
        }
        if ($defaultExpr instanceof ClassConstFetch || $defaultExpr instanceof ConstFetch) {
            return null;
        }
        if ($defaultExpr instanceof UnaryMinus || $defaultExpr instanceof UnaryPlus) {
            return null;
        }
        throw new NotImplementedYetException();
    }
}
