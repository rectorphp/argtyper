<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;

use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Expr\ClassConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\Rector\PhpAttribute\AnnotationToAttributeMapper;
use Argtyper202511\Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
use Argtyper202511\Rector\PhpAttribute\Enum\DocTagNodeState;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
/**
 * @implements AnnotationToAttributeMapperInterface<mixed[]>
 */
final class ArrayAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var \Rector\PhpAttribute\AnnotationToAttributeMapper
     */
    private $annotationToAttributeMapper;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function autowire(AnnotationToAttributeMapper $annotationToAttributeMapper) : void
    {
        $this->annotationToAttributeMapper = $annotationToAttributeMapper;
    }
    /**
     * @param mixed $value
     */
    public function isCandidate($value) : bool
    {
        return \is_array($value);
    }
    /**
     * @param mixed[] $value
     */
    public function map($value) : \Argtyper202511\PhpParser\Node
    {
        $arrayItems = [];
        foreach ($value as $key => $singleValue) {
            $valueExpr = $this->annotationToAttributeMapper->map($singleValue);
            // remove node
            if ($valueExpr === DocTagNodeState::REMOVE_ARRAY) {
                continue;
            }
            // remove value
            if ($this->isRemoveArrayPlaceholder($singleValue)) {
                continue;
            }
            if ($valueExpr instanceof ArrayItem) {
                $valueExpr = $this->resolveValueExprWithSingleQuoteHandling($valueExpr);
                $arrayItems[] = $this->resolveValueExprWithSingleQuoteHandling($valueExpr);
            } else {
                $keyExpr = null;
                if (!\is_int($key)) {
                    $keyExpr = $this->annotationToAttributeMapper->map($key);
                    Assert::isInstanceOf($keyExpr, Expr::class);
                }
                $arrayItems[] = new ArrayItem($valueExpr, $keyExpr);
            }
        }
        return new Array_($arrayItems);
    }
    private function resolveValueExprWithSingleQuoteHandling(ArrayItem $arrayItem) : ArrayItem
    {
        if (!$arrayItem->key instanceof Expr && $arrayItem->value instanceof ClassConstFetch && $arrayItem->value->class instanceof Name && \strpos((string) $arrayItem->value->class, "'") !== \false) {
            $arrayItem->value = new String_($this->valueResolver->getValue($arrayItem->value));
            return $arrayItem;
        }
        return $arrayItem;
    }
    /**
     * @param mixed $value
     */
    private function isRemoveArrayPlaceholder($value) : bool
    {
        if (!\is_array($value)) {
            return \false;
        }
        return \in_array(DocTagNodeState::REMOVE_ARRAY, $value, \true);
    }
}
