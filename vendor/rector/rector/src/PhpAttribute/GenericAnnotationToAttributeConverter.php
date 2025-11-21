<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\AttributeGroup;
use Argtyper202511\PhpParser\Node\Stmt\Use_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Argtyper202511\Rector\Naming\Naming\UseImportsResolver;
use Argtyper202511\Rector\Php80\NodeFactory\AttrGroupsFactory;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
use Argtyper202511\Rector\Php80\ValueObject\DoctrineTagAndAnnotationToAttribute;
/**
 * @api used in Rector packages
 */
final class GenericAnnotationToAttributeConverter
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeFactory\AttrGroupsFactory
     */
    private $attrGroupsFactory;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Naming\Naming\UseImportsResolver
     */
    private $useImportsResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    public function __construct(AttrGroupsFactory $attrGroupsFactory, ReflectionProvider $reflectionProvider, UseImportsResolver $useImportsResolver, PhpDocInfoFactory $phpDocInfoFactory, PhpDocTagRemover $phpDocTagRemover)
    {
        $this->attrGroupsFactory = $attrGroupsFactory;
        $this->reflectionProvider = $reflectionProvider;
        $this->useImportsResolver = $useImportsResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->phpDocTagRemover = $phpDocTagRemover;
    }
    public function convert(Node $node, AnnotationToAttribute $annotationToAttribute): ?AttributeGroup
    {
        if (!$this->isExistingAttributeClass($annotationToAttribute)) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        $uses = $this->useImportsResolver->resolveBareUses();
        return $this->processDoctrineAnnotationClass($phpDocInfo, $uses, $annotationToAttribute);
    }
    /**
     * @param Use_[] $uses
     */
    private function processDoctrineAnnotationClass(PhpDocInfo $phpDocInfo, array $uses, AnnotationToAttribute $annotationToAttribute): ?AttributeGroup
    {
        if ($phpDocInfo->getPhpDocNode()->children === []) {
            return null;
        }
        $doctrineTagAndAnnotationToAttributes = [];
        $doctrineTagValueNodes = [];
        foreach ($phpDocInfo->getPhpDocNode()->children as $phpDocChildNode) {
            if (!$phpDocChildNode instanceof PhpDocTagNode) {
                continue;
            }
            if (!$phpDocChildNode->value instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $doctrineTagValueNode = $phpDocChildNode->value;
            if (!$doctrineTagValueNode->hasClassName($annotationToAttribute->getTag())) {
                continue;
            }
            $doctrineTagAndAnnotationToAttributes[] = new DoctrineTagAndAnnotationToAttribute($doctrineTagValueNode, $annotationToAttribute);
            $doctrineTagValueNodes[] = $doctrineTagValueNode;
        }
        $attributeGroups = $this->attrGroupsFactory->create($doctrineTagAndAnnotationToAttributes, $uses);
        foreach ($doctrineTagValueNodes as $doctrineTagValueNode) {
            $this->phpDocTagRemover->removeTagValueFromNode($phpDocInfo, $doctrineTagValueNode);
        }
        return $attributeGroups[0] ?? null;
    }
    private function isExistingAttributeClass(AnnotationToAttribute $annotationToAttribute): bool
    {
        // make sure the attribute class really exists to avoid error on early upgrade
        if (!$this->reflectionProvider->hasClass($annotationToAttribute->getAttributeClass())) {
            return \false;
        }
        // make sure the class is marked as attribute
        $classReflection = $this->reflectionProvider->getClass($annotationToAttribute->getAttributeClass());
        return $classReflection->isAttributeClass();
    }
}
