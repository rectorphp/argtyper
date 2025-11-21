<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodingStyle\NodeFactory;

use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\MethodCall;
use Argtyper202511\PhpParser\Node\Expr\PropertyFetch;
use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class ArrayCallableToMethodCallFactory
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function create(Array_ $array) : ?MethodCall
    {
        if (\count($array->items) !== 2) {
            return null;
        }
        $firstItem = $array->items[0];
        $secondItem = $array->items[1];
        if (!$firstItem instanceof ArrayItem) {
            return null;
        }
        if (!$secondItem instanceof ArrayItem) {
            return null;
        }
        if (!$secondItem->value instanceof String_) {
            return null;
        }
        if (!$firstItem->value instanceof PropertyFetch && !$firstItem->value instanceof Variable) {
            return null;
        }
        $firstItemType = $this->nodeTypeResolver->getType($firstItem->value);
        $className = ClassNameFromObjectTypeResolver::resolve($firstItemType);
        if ($className === null) {
            return null;
        }
        $string = $secondItem->value;
        $methodName = $string->value;
        return new MethodCall($firstItem->value, $methodName);
    }
}
