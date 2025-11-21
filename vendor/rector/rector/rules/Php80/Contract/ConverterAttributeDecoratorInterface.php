<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\Contract;

use Argtyper202511\PhpParser\Node\Attribute;
interface ConverterAttributeDecoratorInterface
{
    public function getAttributeName(): string;
    public function decorate(Attribute $attribute): void;
}
