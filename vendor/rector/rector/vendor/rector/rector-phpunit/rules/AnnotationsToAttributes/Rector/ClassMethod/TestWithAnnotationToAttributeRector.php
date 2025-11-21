<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Json;
use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Argtyper202511\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.phpunit.de/en/10.0/annotations.html#testwith
 * @changelog https://docs.phpunit.de/en/10.2/attributes.html#testwith
 *
 * @see \Rector\PHPUnit\Tests\AnnotationsToAttributes\Rector\ClassMethod\TestWithAnnotationToAttributeRector\TestWithAnnotationToAttributeRectorTest
 */
final class TestWithAnnotationToAttributeRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var string
     */
    private const TEST_WITH_ATTRIBUTE = 'Argtyper202511\PHPUnit\Framework\Attributes\TestWith';
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory, PhpDocTagRemover $phpDocTagRemover, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory, ReflectionProvider $reflectionProvider)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change @testWith() annotation to #[TestWith] attribute', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeFixture extends TestCase
{
    /**
     * @testWith ["foo"]
     *           ["bar"]
     */
    public function test(): void
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestWith;

final class SomeFixture extends TestCase
{
    #[TestWith(['foo'])]
    #[TestWith(['bar'])]
    public function test(): void
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->testsNodeAnalyzer->isTestClassMethod($node)) {
            return null;
        }
        // make sure the attribute class exists
        if (!$this->reflectionProvider->hasClass(self::TEST_WITH_ATTRIBUTE)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $testWithPhpDocTagNodes = array_merge($phpDocInfo->getTagsByName('testWith'), $phpDocInfo->getTagsByName('testwith'));
        if ($testWithPhpDocTagNodes === []) {
            return null;
        }
        $attributeGroups = [];
        foreach ($testWithPhpDocTagNodes as $testWithPhpDocTagNode) {
            /** @var PhpDocTagNode $testWithPhpDocTagNode */
            if (!$testWithPhpDocTagNode->value instanceof GenericTagValueNode) {
                // not supported yet
                continue;
            }
            // test from doc blocks
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $testWithPhpDocTagNode);
            /** @var GenericTagValueNode $genericTagValueNode */
            $genericTagValueNode = $testWithPhpDocTagNode->value;
            $testWithItems = explode("\n", trim($genericTagValueNode->value));
            foreach ($testWithItems as $testWithItem) {
                $jsonArray = Json::decode(trim($testWithItem), \true);
                $attributeGroups[] = $this->phpAttributeGroupFactory->createFromClassWithItems(self::TEST_WITH_ATTRIBUTE, [$jsonArray]);
            }
        }
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $node->attrGroups = array_merge($node->attrGroups, $attributeGroups);
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::ATTRIBUTES;
    }
}
