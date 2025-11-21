<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;

use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\CurlyListNode;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Argtyper202511\Rector\PhpAttribute\Enum\DocTagNodeState;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @implements AnnotationToAttributeMapperInterface<CurlyListNode>
 */
final class CurlyListNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
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
        return $value instanceof CurlyListNode;
    }
    /**
     * @param CurlyListNode $value
     */
    public function map($value): \Argtyper202511\PhpParser\Node
    {
        $arrayItems = [];
        $arrayItemNodes = $value->getValues();
        $loop = -1;
        foreach ($arrayItemNodes as $arrayItemNode) {
            $valueExpr = $this->annotationToAttributeMapper->map($arrayItemNode);
            // remove node
            if ($valueExpr === DocTagNodeState::REMOVE_ARRAY) {
                continue;
            }
            Assert::isInstanceOf($valueExpr, ArrayItem::class);
            if (!is_numeric($arrayItemNode->key)) {
                $arrayItems[] = $valueExpr;
                continue;
            }
            ++$loop;
            $arrayItemNodeKey = (int) $arrayItemNode->key;
            if ($loop === $arrayItemNodeKey) {
                $arrayItems[] = $valueExpr;
                continue;
            }
            $valueExpr->key = new Int_($arrayItemNodeKey);
            $arrayItems[] = $valueExpr;
        }
        return new Array_($arrayItems);
    }
}
