<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\Contract\ValueObject;

interface AnnotationToAttributeInterface
{
    public function getTag() : string;
    public function getAttributeClass() : string;
}
