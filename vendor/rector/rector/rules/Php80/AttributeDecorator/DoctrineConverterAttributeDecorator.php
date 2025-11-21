<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\AttributeDecorator;

use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\Php80\Contract\ConverterAttributeDecoratorInterface;
final class DoctrineConverterAttributeDecorator implements ConverterAttributeDecoratorInterface
{
    public function getAttributeName() : string
    {
        return 'Argtyper202511\\Doctrine\\ORM\\Mapping\\Column';
    }
    public function decorate(Attribute $attribute) : void
    {
        foreach ($attribute->args as $arg) {
            if (!$arg->name instanceof Identifier) {
                continue;
            }
            if ($arg->name->toString() !== 'nullable') {
                continue;
            }
            $value = $arg->value;
            if (!$value instanceof String_) {
                continue;
            }
            if (!\in_array($value->value, ['true', 'false'], \true)) {
                continue;
            }
            $arg->value = $value->value === 'true' ? new ConstFetch(new Name('true')) : new ConstFetch(new Name('false'));
            break;
        }
    }
}
