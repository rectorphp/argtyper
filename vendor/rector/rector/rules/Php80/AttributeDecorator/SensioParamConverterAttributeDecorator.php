<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\AttributeDecorator;

use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\Rector\Php80\Contract\ConverterAttributeDecoratorInterface;
final class SensioParamConverterAttributeDecorator implements ConverterAttributeDecoratorInterface
{
    public function getAttributeName(): string
    {
        return 'Argtyper202511\Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter';
    }
    public function decorate(Attribute $attribute): void
    {
        // make first named arg silent, @see https://github.com/rectorphp/rector/issues/7352
        $firstArg = $attribute->args[0];
        $firstArg->name = null;
    }
}
