<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\Rector\Property;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\Naming\Naming\UseImportsResolver;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Php80\NodeFactory\NestedAttrGroupsFactory;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationPropertyToAttributeClass;
use Argtyper202511\Rector\Php80\ValueObject\NestedAnnotationToAttribute;
use Argtyper202511\Rector\Php80\ValueObject\NestedDoctrineTagAndAnnotationToAttribute;
use Argtyper202511\Rector\PostRector\Collector\UseNodesToAddCollector;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\ValueObject\PhpVersion;
use Argtyper202511\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Php80\Rector\Property\NestedAnnotationToAttributeRector\NestedAnnotationToAttributeRectorTest
 */
final class NestedAnnotationToAttributeRector extends AbstractRector implements ConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\NestedAttrGroupsFactory
     */
    private $nestedAttrGroupsFactory;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
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
     * @var NestedAnnotationToAttribute[]
     */
    private $nestedAnnotationsToAttributes = [];
    public function __construct(UseImportsResolver $useImportsResolver, PhpDocTagRemover $phpDocTagRemover, NestedAttrGroupsFactory $nestedAttrGroupsFactory, UseNodesToAddCollector $useNodesToAddCollector, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->useImportsResolver = $useImportsResolver;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->nestedAttrGroupsFactory = $nestedAttrGroupsFactory;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change nested annotations to attributes', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeEntity
{
    /**
     * @ORM\JoinTable(name="join_table_name",
     *     joinColumns={@ORM\JoinColumn(name="origin_id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="target_id")}
     * )
     */
    private $collection;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class SomeEntity
{
    #[ORM\JoinTable(name: 'join_table_name')]
    #[ORM\JoinColumn(name: 'origin_id')]
    #[ORM\InverseJoinColumn(name: 'target_id')]
    private $collection;
}
CODE_SAMPLE
, [new NestedAnnotationToAttribute('Argtyper202511\Doctrine\ORM\Mapping\JoinTable', [new AnnotationPropertyToAttributeClass('Argtyper202511\Doctrine\ORM\Mapping\JoinColumn', 'joinColumns'), new AnnotationPropertyToAttributeClass('Argtyper202511\Doctrine\ORM\Mapping\InverseJoinColumn', 'inverseJoinColumns')])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class, Class_::class, Param::class];
    }
    /**
     * @param Property|Class_|Param $node
     */
    public function refactor(Node $node): ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $uses = $this->useImportsResolver->resolveBareUses();
        $attributeGroups = $this->transformDoctrineAnnotationClassesToAttributeGroups($phpDocInfo, $uses);
        if ($attributeGroups === []) {
            return null;
        }
        // 3. Reprint docblock
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
        $node->attrGroups = array_merge($node->attrGroups, $attributeGroups);
        $this->completeExtraUseImports($attributeGroups);
        return $node;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration): void
    {
        Assert::allIsInstanceOf($configuration, NestedAnnotationToAttribute::class);
        $this->nestedAnnotationsToAttributes = $configuration;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersion::PHP_80;
    }
    /**
     * @param Use_[] $uses
     * @return AttributeGroup[]
     */
    private function transformDoctrineAnnotationClassesToAttributeGroups(PhpDocInfo $phpDocInfo, array $uses): array
    {
        if ($phpDocInfo->getPhpDocNode()->children === []) {
            return [];
        }
        $nestedDoctrineTagAndAnnotationToAttributes = [];
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $doctrineTagValueNode = $phpDocChildNode->value;
            $nestedAnnotationToAttribute = $this->matchAnnotationToAttribute($doctrineTagValueNode);
            if (!$nestedAnnotationToAttribute instanceof NestedAnnotationToAttribute) {
                continue;
            }
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNode);
            $nestedDoctrineTagAndAnnotationToAttributes[] = new NestedDoctrineTagAndAnnotationToAttribute($doctrineTagValueNode, $nestedAnnotationToAttribute);
        }
        return $this->nestedAttrGroupsFactory->create($nestedDoctrineTagAndAnnotationToAttributes, $uses);
    }
    private function matchAnnotationToAttribute(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode): ?\Argtyper202511\Rector\Php80\ValueObject\NestedAnnotationToAttribute
    {
        $doctrineResolvedClass = $doctrineAnnotationTagValueNode->identifierTypeNode->getAttribute(PhpDocAttributeKey::RESOLVED_CLASS);
        foreach ($this->nestedAnnotationsToAttributes as $nestedAnnotationToAttribute) {
            foreach ($nestedAnnotationToAttribute->getAnnotationPropertiesToAttributeClasses() as $annotationClass) {
                if ($annotationClass->getAttributeClass() === $doctrineResolvedClass) {
                    return $nestedAnnotationToAttribute;
                }
            }
            if ($doctrineResolvedClass !== $nestedAnnotationToAttribute->getTag()) {
                continue;
            }
            return $nestedAnnotationToAttribute;
        }
        return null;
    }
    /**
     * @param AttributeGroup[] $attributeGroups
     */
    private function completeExtraUseImports(array $attributeGroups): void
    {
        foreach ($attributeGroups as $attributeGroup) {
            foreach ($attributeGroup->attrs as $attr) {
                $namespacedAttrName = $attr->name->getAttribute(AttributeKey::EXTRA_USE_IMPORT);
                if (!is_string($namespacedAttrName)) {
                    continue;
                }
                $this->useNodesToAddCollector->addUseImport(new FullyQualifiedObjectType($namespacedAttrName));
            }
        }
    }
}
