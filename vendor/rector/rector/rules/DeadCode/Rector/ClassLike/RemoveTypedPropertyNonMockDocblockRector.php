<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\ClassLike;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Name\FullyQualified;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\VarTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Argtyper202511\Rector\Enum\ClassName;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\StaticTypeMapper;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassLike\RemoveTypedPropertyNonMockDocblockRector\RemoveTypedPropertyNonMockDocblockRectorTest
 */
final class RemoveTypedPropertyNonMockDocblockRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover
     */
    private $varTagRemover;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\StaticTypeMapper
     */
    private $staticTypeMapper;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(VarTagRemover $varTagRemover, StaticTypeMapper $staticTypeMapper, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->varTagRemover = $varTagRemover;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove @var annotation for PHPUnit\\Framework\\MockObject\\MockObject combined with native object type', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    /**
     * @var SomeClass|MockObject
     */
    private SomeClass $someProperty;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

final class SomeTest extends TestCase
{
    private SomeClass $someProperty;
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType(ClassName::TEST_CASE_CLASS))) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getProperties() as $property) {
            // not yet typed
            if (!$property->type instanceof Node) {
                continue;
            }
            if (\count($property->props) !== 1) {
                continue;
            }
            if (!$property->type instanceof FullyQualified) {
                continue;
            }
            if ($this->isObjectType($property->type, new ObjectType(ClassName::MOCK_OBJECT))) {
                continue;
            }
            $propertyDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if (!$this->isVarTagUnionTypeMockObject($propertyDocInfo, $property)) {
                continue;
            }
            // clear var docblock
            if ($this->varTagRemover->removeVarTag($property)) {
                $hasChanged = \true;
            }
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    private function isVarTagUnionTypeMockObject(PhpDocInfo $phpDocInfo, Property $property) : bool
    {
        $varTagValueNode = $phpDocInfo->getVarTagValueNode();
        if (!$varTagValueNode instanceof VarTagValueNode) {
            return \false;
        }
        if (!$varTagValueNode->type instanceof UnionTypeNode) {
            return \false;
        }
        $varTagType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($varTagValueNode->type, $property);
        if (!$varTagType instanceof UnionType) {
            return \false;
        }
        foreach ($varTagType->getTypes() as $unionedType) {
            if ($unionedType->isSuperTypeOf(new ObjectType(ClassName::MOCK_OBJECT))->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
