<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\InterpolatedStringPart;
use Argtyper202511\PhpParser\Node\Scalar;
use Argtyper202511\PhpParser\Node\Scalar\Float_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\InterpolatedString;
use Argtyper202511\PhpParser\Node\Scalar\MagicConst;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Type\Constant\ConstantFloatType;
use Argtyper202511\PHPStan\Type\Constant\ConstantIntegerType;
use Argtyper202511\PHPStan\Type\Constant\ConstantStringType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Rector\Exception\NotImplementedYetException;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @implements NodeTypeResolverInterface<Scalar>
 */
final class ScalarTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses(): array
    {
        return [Scalar::class];
    }
    public function resolve(Node $node): Type
    {
        if ($node instanceof Float_) {
            return new ConstantFloatType($node->value);
        }
        if ($node instanceof String_) {
            return new ConstantStringType($node->value);
        }
        if ($node instanceof Int_) {
            return new ConstantIntegerType($node->value);
        }
        if ($node instanceof MagicConst) {
            return new ConstantStringType($node->getName());
        }
        if ($node instanceof InterpolatedString) {
            return new StringType();
        }
        if ($node instanceof InterpolatedStringPart) {
            return new ConstantStringType($node->value);
        }
        throw new NotImplementedYetException();
    }
}
