<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\Rector\Php74\Guard\MakePropertyTypedGuard;
use Argtyper202511\Rector\PHPStan\ScopeFetcher;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\TypeDeclaration\NodeAnalyzer\JMSTypeAnalyzer;
use Argtyper202511\Rector\TypeDeclaration\NodeFactory\JMSTypePropertyTypeFactory;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\ObjectTypedPropertyFromJMSSerializerAttributeTypeRector\ObjectTypedPropertyFromJMSSerializerAttributeTypeRectorTest
 */
final class ObjectTypedPropertyFromJMSSerializerAttributeTypeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Php74\Guard\MakePropertyTypedGuard
     */
    private $makePropertyTypedGuard;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeAnalyzer\JMSTypeAnalyzer
     */
    private $jmsTypeAnalyzer;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\NodeFactory\JMSTypePropertyTypeFactory
     */
    private $jmsTypePropertyTypeFactory;
    public function __construct(MakePropertyTypedGuard $makePropertyTypedGuard, JMSTypeAnalyzer $jmsTypeAnalyzer, JMSTypePropertyTypeFactory $jmsTypePropertyTypeFactory)
    {
        $this->makePropertyTypedGuard = $makePropertyTypedGuard;
        $this->jmsTypeAnalyzer = $jmsTypeAnalyzer;
        $this->jmsTypePropertyTypeFactory = $jmsTypePropertyTypeFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add object typed property from JMS Serializer Type attribute', [new CodeSample(<<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Type;

final class SomeClass
{
    #[Type(Product::class)]
    private $product;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use JMS\Serializer\Annotation\Type;

final class SomeClass
{
    #[Type(Product::class)]
    private ?Product $product = null;
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->jmsTypeAnalyzer->hasAtLeastOneUntypedPropertyUsingJmsAttribute($node)) {
            return null;
        }
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            if ($this->shouldSkipProperty($property, $classReflection)) {
                continue;
            }
            $typeValue = $this->jmsTypeAnalyzer->resolveTypeAttributeValue($property);
            if (!\is_string($typeValue)) {
                continue;
            }
            $propertyTypeNode = $this->jmsTypePropertyTypeFactory->createObjectTypeNode($typeValue);
            if (!$propertyTypeNode instanceof FullyQualified) {
                continue;
            }
            $property->type = new NullableType($propertyTypeNode);
            $property->props[0]->default = $this->nodeFactory->createNull();
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function shouldSkipProperty(Property $property, ClassReflection $classReflection) : bool
    {
        if ($property->type instanceof Node || $property->props[0]->default instanceof Expr) {
            return \true;
        }
        if (!$this->jmsTypeAnalyzer->hasPropertyJMSTypeAttribute($property)) {
            return \true;
        }
        return !$this->makePropertyTypedGuard->isLegal($property, $classReflection);
    }
}
