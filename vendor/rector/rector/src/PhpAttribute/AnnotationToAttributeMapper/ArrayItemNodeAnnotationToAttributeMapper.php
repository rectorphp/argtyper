<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\PhpAttribute\AnnotationToAttributeMapper;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Rector\PhpAttribute\Enum\DocTagNodeState;
use Rector\Validation\RectorAssert;
use RectorPrefix202511\Webmozart\Assert\InvalidArgumentException;
/**
 * @implements AnnotationToAttributeMapperInterface<ArrayItemNode>
 */
final class ArrayItemNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    /**
     * Avoid circular reference
     */
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper): void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }
    /**
     * @param mixed $value
     */
    public function isCandidate($value): bool
    {
        return $value instanceof ArrayItemNode;
    }
    /**
     * @param ArrayItemNode $arrayItemNode
     */
    public function map($arrayItemNode): \Argtyper202511\PhpParser\Node
    {
        $valueExpr = $this->annotationToAttributeMapper->map($arrayItemNode->value);
        if ($valueExpr === DocTagNodeState::REMOVE_ARRAY) {
            return new ArrayItem(new String_($valueExpr));
        }
        if ($arrayItemNode->key !== null) {
            /** @var Expr $keyExpr */
            $keyExpr = $this->annotationToAttributeMapper->map($arrayItemNode->key);
        } else {
            if ($this->hasNoParenthesesAnnotation($arrayItemNode)) {
                try {
                    RectorAssert::className(ltrim((string) $arrayItemNode->value, '@'));
                    $identifierTypeNode = new IdentifierTypeNode($arrayItemNode->value);
                    $arrayItemNode->value = new DoctrineAnnotationTagValueNode($identifierTypeNode);
                    return $this->map($arrayItemNode);
                } catch (InvalidArgumentException $exception) {
                }
            }
            $keyExpr = null;
        }
        // @todo how to skip natural integer keys?
        return new ArrayItem($valueExpr, $keyExpr);
    }
    private function hasNoParenthesesAnnotation(ArrayItemNode $arrayItemNode): bool
    {
        if ($arrayItemNode->value instanceof StringNode) {
            return \false;
        }
        if (!is_string($arrayItemNode->value)) {
            return \false;
        }
        if (strncmp($arrayItemNode->value, '@', strlen('@')) !== 0) {
            return \false;
        }
        return substr_compare($arrayItemNode->value, ')', -strlen(')')) !== 0;
    }
}
