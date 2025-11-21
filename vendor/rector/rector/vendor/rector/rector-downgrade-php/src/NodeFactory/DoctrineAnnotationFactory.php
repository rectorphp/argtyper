<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Attribute;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PhpParser\Printer\BetterStandardPrinter;
final class DoctrineAnnotationFactory
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(BetterStandardPrinter $betterStandardPrinter)
    {
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    /**
     * @api downgrade
     */
    public function createFromAttribute(Attribute $attribute, string $className) : DoctrineAnnotationTagValueNode
    {
        $items = $this->createItemsFromArgs($attribute->args);
        $identifierTypeNode = new IdentifierTypeNode($className);
        return new DoctrineAnnotationTagValueNode($identifierTypeNode, null, $items);
    }
    /**
     * @param Arg[] $args
     * @return mixed[]
     */
    private function createItemsFromArgs(array $args) : array
    {
        $items = [];
        foreach ($args as $arg) {
            if ($arg->value instanceof String_) {
                // standardize double quotes for annotations
                $arg->value->setAttribute(AttributeKey::KIND, String_::KIND_DOUBLE_QUOTED);
            }
            $itemValue = $this->betterStandardPrinter->print($arg->value);
            if ($arg->name instanceof Identifier) {
                $name = $this->betterStandardPrinter->print($arg->name);
                $items[$name] = $itemValue;
            } else {
                $items[] = $itemValue;
            }
        }
        return $items;
    }
}
