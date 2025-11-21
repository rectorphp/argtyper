<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;

use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\New_;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
use Argtyper202511\Rector\Php\PhpVersionProvider;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\AttributeArrayNameInliner;
use Argtyper202511\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Argtyper202511\Rector\ValueObject\PhpVersionFeature;
/**
 * @implements AnnotationToAttributeMapperInterface<DoctrineAnnotationTagValueNode>
 */
final class DoctrineAnnotationAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\AttributeArrayNameInliner
     */
    private $attributeArrayNameInliner;
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    public function __construct(PhpVersionProvider $phpVersionProvider, AttributeArrayNameInliner $attributeArrayNameInliner)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->attributeArrayNameInliner = $attributeArrayNameInliner;
    }
    /**
     * Avoid circular reference
     */
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper) : void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        if (!$value instanceof DoctrineAnnotationTagValueNode) {
            return \false;
        }
        return $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::NEW_INITIALIZERS);
    }
    /**
     * @param DoctrineAnnotationTagValueNode $value
     */
    public function map($value) : \Argtyper202511\PhpParser\Node
    {
        $annotationShortName = $this->resolveAnnotationName($value);
        $values = $value->getValues();
        if ($values !== []) {
            $argValues = $this->annotationToAttributeMapper->map($value->getValues());
            if ($argValues instanceof Array_) {
                // create named args
                $args = $this->attributeArrayNameInliner->inlineArrayToArgs($argValues);
            } else {
                throw new ShouldNotHappenException();
            }
        } else {
            $args = [];
        }
        return new New_(new Name($annotationShortName), $args);
    }
    private function resolveAnnotationName(DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode) : string
    {
        $annotationShortName = $doctrineAnnotationTagValueNode->identifierTypeNode->name;
        return \ltrim($annotationShortName, '@');
    }
}
