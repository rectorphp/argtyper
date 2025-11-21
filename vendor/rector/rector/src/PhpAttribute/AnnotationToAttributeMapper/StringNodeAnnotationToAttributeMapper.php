<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Argtyper202511\Rector\NodeTypeResolver\Node\AttributeKey;
use Argtyper202511\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<StringNode>
 */
final class StringNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value): bool
    {
        return $value instanceof StringNode;
    }
    /**
     * @param StringNode $value
     */
    public function map($value): \Argtyper202511\PhpParser\Node
    {
        return new String_($value->value, [AttributeKey::KIND => $value->getAttribute(AttributeKey::KIND)]);
    }
}
