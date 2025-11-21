<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\ValueObject;

use Argtyper202511\Rector\Php80\Contract\ValueObject\AnnotationToAttributeInterface;
use Argtyper202511\Rector\Validation\RectorAssert;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class AnnotationToAttribute implements AnnotationToAttributeInterface
{
    /**
     * @readonly
     * @var string
     */
    private $tag;
    /**
     * @readonly
     * @var string|null
     */
    private $attributeClass;
    /**
     * @var string[]
     * @readonly
     */
    private $classReferenceFields = [];
    /**
     * @readonly
     * @var bool
     */
    private $useValueAsAttributeArgument = \false;
    /**
     * @param string[] $classReferenceFields
     */
    public function __construct(string $tag, ?string $attributeClass = null, array $classReferenceFields = [], bool $useValueAsAttributeArgument = \false)
    {
        $this->tag = $tag;
        $this->attributeClass = $attributeClass;
        $this->classReferenceFields = $classReferenceFields;
        $this->useValueAsAttributeArgument = $useValueAsAttributeArgument;
        RectorAssert::className($tag);
        if (\is_string($attributeClass)) {
            RectorAssert::className($attributeClass);
        }
        Assert::allString($classReferenceFields);
    }
    public function getTag() : string
    {
        return $this->tag;
    }
    public function getAttributeClass() : string
    {
        if ($this->attributeClass === null) {
            return $this->tag;
        }
        return $this->attributeClass;
    }
    /**
     * @return string[]
     */
    public function getClassReferenceFields() : array
    {
        return $this->classReferenceFields;
    }
    public function getUseValueAsAttributeArgument() : bool
    {
        return $this->useValueAsAttributeArgument;
    }
}
