<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\ValueObject;

final class AttributeValueAndDocComment
{
    /**
     * @readonly
     * @var string
     */
    public $attributeValue;
    /**
     * @readonly
     * @var string
     */
    public $docComment;
    public function __construct(string $attributeValue, string $docComment)
    {
        $this->attributeValue = $attributeValue;
        $this->docComment = $docComment;
    }
}
