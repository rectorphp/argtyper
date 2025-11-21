<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Rector\Class_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Param;
use Argtyper202511\PhpParser\Node\Stmt\Class_;
use Argtyper202511\PhpParser\Node\Stmt\ClassMethod;
use Argtyper202511\PhpParser\Node\Stmt\Function_;
use Argtyper202511\PhpParser\Node\Stmt\Interface_;
use Argtyper202511\PhpParser\Node\Stmt\Property;
use Argtyper202511\PHPStan\Analyser\Scope;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Argtyper202511\Rector\Contract\Rector\ConfigurableRectorInterface;
use Argtyper202511\Rector\DowngradePhp80\ValueObject\DowngradeAttributeToAnnotation;
use Argtyper202511\Rector\NodeFactory\DoctrineAnnotationFactory;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @changelog https://php.watch/articles/php-attributes#syntax
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\DowngradeAttributeToAnnotationRectorTest
 */
final class DowngradeAttributeToAnnotationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\NodeFactory\DoctrineAnnotationFactory
     */
    private $doctrineAnnotationFactory;
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
     * @var string[]
     */
    private const SKIPPED_ATTRIBUTES = ['Attribute', 'ReturnTypeWillChange', 'AllowDynamicProperties', 'Override'];
    /**
     * @var DowngradeAttributeToAnnotation[]
     */
    private $attributesToAnnotations = [];
    /**
     * @var bool
     */
    private $isDowngraded = \false;
    public function __construct(DoctrineAnnotationFactory $doctrineAnnotationFactory, DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->doctrineAnnotationFactory = $doctrineAnnotationFactory;
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor PHP attribute markers to annotations notation', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    #[Route(path: '/path', name: 'action')]
    public function action()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SymfonyRoute
{
    /**
     * @Route("/path", name="action")
     */
    public function action()
    {
    }
}
CODE_SAMPLE
, [new DowngradeAttributeToAnnotation('Argtyper202511\\Symfony\\Component\\Routing\\Annotation\\Route')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, ClassMethod::class, Property::class, Interface_::class, Param::class, Function_::class];
    }
    /**
     * @param Class_|ClassMethod|Property|Interface_|Param|Function_  $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->attrGroups === []) {
            return null;
        }
        $this->isDowngraded = \false;
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $oldTokens = $this->file->getOldTokens();
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $key => $attribute) {
                if ($this->shouldSkipAttribute($attribute)) {
                    if (isset($oldTokens[$attrGroup->getEndTokenPos() + 1]) && \strpos((string) $oldTokens[$attrGroup->getEndTokenPos() + 1], "\n") === \false) {
                        // add new line
                        $oldTokens[$attrGroup->getEndTokenPos() + 1]->text = "\n" . $this->resolveIndentation($node, $attrGroup, $attribute) . $oldTokens[$attrGroup->getEndTokenPos() + 1]->text;
                        $this->isDowngraded = \true;
                    }
                    continue;
                }
                $attributeToAnnotation = $this->matchAttributeToAnnotation($attribute, $this->attributesToAnnotations);
                if (!$attributeToAnnotation instanceof DowngradeAttributeToAnnotation) {
                    // clear the attribute to avoid inlining to a comment that will ignore the rest of the line
                    unset($attrGroup->attrs[$key]);
                    continue;
                }
                unset($attrGroup->attrs[$key]);
                $this->isDowngraded = \true;
                if (\strpos($attributeToAnnotation->getTag(), '\\') === \false) {
                    $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@' . $attributeToAnnotation->getTag(), new GenericTagValueNode('')));
                    $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
                    continue;
                }
                $doctrineAnnotation = $this->doctrineAnnotationFactory->createFromAttribute($attribute, $attributeToAnnotation->getTag());
                $phpDocInfo->addTagValueNode($doctrineAnnotation);
                $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($node);
            }
        }
        // cleanup empty attr groups
        $this->cleanupEmptyAttrGroups($node);
        if (!$this->isDowngraded) {
            return null;
        }
        return $node;
    }
    /**
     * @param DowngradeAttributeToAnnotation[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, DowngradeAttributeToAnnotation::class);
        $this->attributesToAnnotations = $configuration;
    }
    private function resolveIndentation(Node $node, AttributeGroup $attributeGroup, Attribute $attribute) : string
    {
        $scope = $node->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return '';
        }
        if ($node->getStartTokenPos() === \strlen($attribute->name->toString()) - 2 || $node instanceof Class_ && $scope->isInFirstLevelStatement()) {
            return '';
        }
        $indent = $attributeGroup->getEndTokenPos() - $node->getStartTokenPos() + 2;
        return $indent > 0 ? \str_repeat(' ', $indent) : '';
    }
    /**
     * @param \PhpParser\Node\Stmt\ClassMethod|\PhpParser\Node\Stmt\Property|\PhpParser\Node\Stmt\Class_|\PhpParser\Node\Stmt\Interface_|\PhpParser\Node\Param|\PhpParser\Node\Stmt\Function_ $node
     */
    private function cleanupEmptyAttrGroups($node) : void
    {
        foreach ($node->attrGroups as $key => $attrGroup) {
            if ($attrGroup->attrs !== []) {
                continue;
            }
            unset($node->attrGroups[$key]);
            $this->isDowngraded = \true;
        }
    }
    /**
     * @param DowngradeAttributeToAnnotation[] $attributesToAnnotations
     */
    private function matchAttributeToAnnotation(Attribute $attribute, array $attributesToAnnotations) : ?DowngradeAttributeToAnnotation
    {
        foreach ($attributesToAnnotations as $attributeToAnnotation) {
            if (!$this->isName($attribute->name, $attributeToAnnotation->getAttributeClass())) {
                continue;
            }
            return $attributeToAnnotation;
        }
        return null;
    }
    private function shouldSkipAttribute(Attribute $attribute) : bool
    {
        $attributeName = $attribute->name->toString();
        return \in_array($attributeName, self::SKIPPED_ATTRIBUTES, \true);
    }
}
