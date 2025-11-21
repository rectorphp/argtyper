<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\PhpDocNodeVisitor;

use Argtyper202511\RectorPrefix202511\Nette\Utils\Strings;
use Argtyper202511\PhpParser\Node as PhpParserNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Node;
use Argtyper202511\PHPStan\PhpDocParser\Ast\PhpDoc\TemplateTagValueNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Application\Provider\CurrentFileProvider;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Argtyper202511\Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\PhpDocParser\PhpDocParser\PhpDocNodeVisitor\AbstractPhpDocNodeVisitor;
use Argtyper202511\Rector\PostRector\Collector\UseNodesToAddCollector;
use Argtyper202511\Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Argtyper202511\Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
use Argtyper202511\Rector\ValueObject\Application\File;
final class NameImportingPhpDocNodeVisitor extends AbstractPhpDocNodeVisitor
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\ClassNameImport\ClassNameImportSkipper
     */
    private $classNameImportSkipper;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\Application\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\StaticTypeMapper\PhpDocParser\IdentifierPhpDocTypeMapper
     */
    private $identifierPhpDocTypeMapper;
    /**
     * @var PhpParserNode|null
     */
    private $currentPhpParserNode;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(ClassNameImportSkipper $classNameImportSkipper, UseNodesToAddCollector $useNodesToAddCollector, CurrentFileProvider $currentFileProvider, ReflectionProvider $reflectionProvider, IdentifierPhpDocTypeMapper $identifierPhpDocTypeMapper)
    {
        $this->classNameImportSkipper = $classNameImportSkipper;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->currentFileProvider = $currentFileProvider;
        $this->reflectionProvider = $reflectionProvider;
        $this->identifierPhpDocTypeMapper = $identifierPhpDocTypeMapper;
    }
    public function beforeTraverse(\Argtyper202511\PHPStan\PhpDocParser\Ast\Node $node) : void
    {
        if (!$this->currentPhpParserNode instanceof PhpParserNode) {
            throw new ShouldNotHappenException('Set "$currentPhpParserNode" first');
        }
    }
    public function enterNode(Node $node) : ?Node
    {
        if ($node instanceof SpacelessPhpDocTagNode) {
            return $this->enterSpacelessPhpDocTagNode($node);
        }
        if ($node instanceof DoctrineAnnotationTagValueNode) {
            $this->processDoctrineAnnotationTagValueNode($node);
            return $node;
        }
        if (!$node instanceof IdentifierTypeNode) {
            return null;
        }
        if (!$this->currentPhpParserNode instanceof PhpParserNode) {
            throw new ShouldNotHappenException();
        }
        // no \, skip early
        if (\strpos($node->name, '\\') === \false) {
            return null;
        }
        $staticType = $this->identifierPhpDocTypeMapper->mapIdentifierTypeNode($node, $this->currentPhpParserNode);
        $staticType = $this->resolveFullyQualified($staticType);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            return null;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        return $this->processFqnNameImport($this->currentPhpParserNode, $node, $staticType, $file);
    }
    public function setCurrentNode(PhpParserNode $phpParserNode) : void
    {
        $this->hasChanged = \false;
        $this->currentPhpParserNode = $phpParserNode;
    }
    public function hasChanged() : bool
    {
        return $this->hasChanged;
    }
    private function resolveFullyQualified(Type $type) : ?FullyQualifiedObjectType
    {
        if ($type instanceof ShortenedObjectType || $type instanceof AliasedObjectType) {
            return new FullyQualifiedObjectType($type->getFullyQualifiedName());
        }
        if ($type instanceof FullyQualifiedObjectType) {
            return $type;
        }
        return null;
    }
    private function processFqnNameImport(PhpParserNode $phpParserNode, IdentifierTypeNode $identifierTypeNode, FullyQualifiedObjectType $fullyQualifiedObjectType, File $file) : ?IdentifierTypeNode
    {
        $parentNode = $identifierTypeNode->getAttribute(PhpDocAttributeKey::PARENT);
        if ($parentNode instanceof TemplateTagValueNode) {
            // might break
            return null;
        }
        // standardize to FQN
        if (\strncmp($fullyQualifiedObjectType->getClassName(), '@', \strlen('@')) === 0) {
            $fullyQualifiedObjectType = new FullyQualifiedObjectType(\ltrim($fullyQualifiedObjectType->getClassName(), '@'));
        }
        if ($this->classNameImportSkipper->shouldSkipNameForFullyQualifiedObjectType($file, $phpParserNode, $fullyQualifiedObjectType)) {
            return null;
        }
        $newNode = new IdentifierTypeNode($fullyQualifiedObjectType->getShortName());
        // should skip because its already used
        if ($this->useNodesToAddCollector->isShortImported($file, $fullyQualifiedObjectType) && !$this->useNodesToAddCollector->isImportShortable($file, $fullyQualifiedObjectType)) {
            return null;
        }
        if ($this->shouldImport($file, $newNode, $identifierTypeNode, $fullyQualifiedObjectType)) {
            $this->useNodesToAddCollector->addUseImport($fullyQualifiedObjectType);
            $this->hasChanged = \true;
            return $newNode;
        }
        return null;
    }
    private function shouldImport(File $file, IdentifierTypeNode $newNode, IdentifierTypeNode $identifierTypeNode, FullyQualifiedObjectType $fullyQualifiedObjectType) : bool
    {
        if ($newNode->name === $identifierTypeNode->name) {
            return \false;
        }
        if (\strncmp($identifierTypeNode->name, '\\', \strlen('\\')) === 0) {
            if ($fullyQualifiedObjectType->getShortName() !== $fullyQualifiedObjectType->getClassName()) {
                return $fullyQualifiedObjectType->getShortName() !== \ltrim($identifierTypeNode->name, '\\');
            }
            return \true;
        }
        $className = $fullyQualifiedObjectType->getClassName();
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        $firstPath = Strings::before($identifierTypeNode->name, '\\' . $newNode->name);
        if ($firstPath === null) {
            return !$this->useNodesToAddCollector->hasImport($file, $fullyQualifiedObjectType);
        }
        if ($firstPath === '') {
            return \true;
        }
        $namespaceParts = \explode('\\', \ltrim($firstPath, '\\'));
        return \count($namespaceParts) > 1;
    }
    private function processDoctrineAnnotationTagValueNode(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : void
    {
        $currentPhpParserNode = $this->currentPhpParserNode;
        if (!$currentPhpParserNode instanceof PhpParserNode) {
            throw new ShouldNotHappenException();
        }
        $identifierTypeNode = $doctrineAnnotationTagValueNode->identifierTypeNode;
        $staticType = $this->identifierPhpDocTypeMapper->mapIdentifierTypeNode($identifierTypeNode, $currentPhpParserNode);
        $staticType = $this->resolveFullyQualified($staticType);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            return;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return;
        }
        $shortentedIdentifierTypeNode = $this->processFqnNameImport($currentPhpParserNode, $identifierTypeNode, $staticType, $file);
        if (!$shortentedIdentifierTypeNode instanceof IdentifierTypeNode) {
            return;
        }
        $doctrineAnnotationTagValueNode->identifierTypeNode = $shortentedIdentifierTypeNode;
        $doctrineAnnotationTagValueNode->markAsChanged();
    }
    private function enterSpacelessPhpDocTagNode(SpacelessPhpDocTagNode $spacelessPhpDocTagNode) : ?\Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\SpacelessPhpDocTagNode
    {
        if (!$spacelessPhpDocTagNode->value instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        // special case for doctrine annotation
        if (\strncmp($spacelessPhpDocTagNode->name, '@', \strlen('@')) !== 0) {
            return null;
        }
        $attributeClass = \ltrim($spacelessPhpDocTagNode->name, '@\\');
        $identifierTypeNode = new IdentifierTypeNode($attributeClass);
        $currentPhpParserNode = $this->currentPhpParserNode;
        if (!$currentPhpParserNode instanceof PhpParserNode) {
            throw new ShouldNotHappenException();
        }
        $staticType = $this->identifierPhpDocTypeMapper->mapIdentifierTypeNode(new IdentifierTypeNode($attributeClass), $currentPhpParserNode);
        $staticType = $this->resolveFullyQualified($staticType);
        if (!$staticType instanceof FullyQualifiedObjectType) {
            return null;
        }
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            return null;
        }
        $importedName = $this->processFqnNameImport($currentPhpParserNode, $identifierTypeNode, $staticType, $file);
        if ($importedName instanceof IdentifierTypeNode) {
            $spacelessPhpDocTagNode->name = '@' . $importedName->name;
            return $spacelessPhpDocTagNode;
        }
        return null;
    }
}
