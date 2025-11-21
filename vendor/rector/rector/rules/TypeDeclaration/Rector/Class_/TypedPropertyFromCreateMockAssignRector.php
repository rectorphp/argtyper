<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\IntersectionType;
use Argtyper202511\PhpParser\Node\NullableType;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Enum\ClassName;
use Argtyper202511\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector;
use Argtyper202511\Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Class_\TypedPropertyFromCreateMockAssignRector\TypedPropertyFromCreateMockAssignRectorTest
 */
final class TypedPropertyFromCreateMockAssignRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\TypeInferer\AssignToPropertyTypeInferer
     */
    private $assignToPropertyTypeInferer;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\TypeDeclaration\AlreadyAssignDetector\ConstructorAssignDetector
     */
    private $constructorAssignDetector;
    public function __construct(AssignToPropertyTypeInferer $assignToPropertyTypeInferer, StaticTypeMapper $staticTypeMapper, ConstructorAssignDetector $constructorAssignDetector)
    {
        $this->assignToPropertyTypeInferer = $assignToPropertyTypeInferer;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->constructorAssignDetector = $constructorAssignDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add "PHPUnit\Framework\MockObject\MockObject" typed property from assigned mock to clearly separate from real objects', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    private $someProperty;

    protected function setUp(): void
    {
        $this->someProperty = $this->createMock(SomeMockedClass::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    private MockObject $someProperty;

    protected function setUp(): void
    {
        $this->someProperty = $this->createMock(SomeMockedClass::class);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isObjectType($node, new ObjectType(ClassName::TEST_CASE_CLASS))) {
            return null;
        }
        $hasChanged = \false;
        $mockObjectType = new ObjectType(ClassName::MOCK_OBJECT);
        foreach ($node->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }
            // already use PHPUnit\Framework\MockObject\MockObject type
            if ($this->isAlreadyTypedWithMockObject($property, $mockObjectType)) {
                continue;
            }
            $propertyName = (string) $this->getName($property);
            $type = $this->assignToPropertyTypeInferer->inferPropertyInClassLike($property, $propertyName, $node);
            if (!$type instanceof Type) {
                continue;
            }
            $propertyType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($type, TypeKind::PROPERTY);
            if (!$propertyType instanceof Node) {
                continue;
            }
            if (!$this->isObjectType($propertyType, $mockObjectType)) {
                continue;
            }
            if (!$this->constructorAssignDetector->isPropertyAssigned($node, $propertyName)) {
                if (!$propertyType instanceof NullableType) {
                    continue;
                }
                $property->props[0]->default = $this->nodeFactory->createNull();
            }
            $property->type = $propertyType;
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function isAlreadyTypedWithMockObject(Property $property, ObjectType $mockObjectType): bool
    {
        if (!$property->type instanceof Node) {
            return \false;
        }
        // complex type, used on purpose
        if ($property->type instanceof IntersectionType) {
            return \true;
        }
        return $this->isObjectType($property->type, $mockObjectType);
    }
}
