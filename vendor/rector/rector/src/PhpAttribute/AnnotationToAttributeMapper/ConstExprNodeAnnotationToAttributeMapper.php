<?php

declare (strict_types=1);
namespace Rector\PhpAttribute\AnnotationToAttributeMapper;

use Argtyper202511\PhpParser\BuilderHelpers;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use Argtyper202511\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\Constant\ConstantFloatType;
use Rector\Exception\NotImplementedYetException;
use Rector\PhpAttribute\Contract\AnnotationToAttributeMapperInterface;
/**
 * @implements AnnotationToAttributeMapperInterface<ConstExprNode>
 */
final class ConstExprNodeAnnotationToAttributeMapper implements AnnotationToAttributeMapperInterface
{
    /**
     * @param mixed $value
     */
    public function isCandidate($value): bool
    {
        return $value instanceof ConstExprNode;
    }
    /**
     * @param ConstExprNode $value
     */
    public function map($value): \Argtyper202511\PhpParser\Node
    {
        if ($value instanceof ConstExprIntegerNode) {
            return BuilderHelpers::normalizeValue((int) $value->value);
        }
        if ($value instanceof ConstantFloatType || $value instanceof ConstantBooleanType) {
            return BuilderHelpers::normalizeValue($value->getValue());
        }
        if ($value instanceof ConstExprTrueNode) {
            return BuilderHelpers::normalizeValue(\true);
        }
        if ($value instanceof ConstExprFalseNode) {
            return BuilderHelpers::normalizeValue(\false);
        }
        throw new NotImplementedYetException();
    }
}
