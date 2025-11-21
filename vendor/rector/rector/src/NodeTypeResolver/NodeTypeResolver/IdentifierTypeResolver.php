<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Identifier;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\IterableType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\ObjectWithoutClassType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @implements NodeTypeResolverInterface<Identifier>
 */
final class IdentifierTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Identifier::class];
    }
    /**
     * @param Identifier $node
     * @return StringType|BooleanType|ConstantBooleanType|NullType|ObjectWithoutClassType|ArrayType|IterableType|IntegerType|FloatType|MixedType
     */
    public function resolve(Node $node) : Type
    {
        $lowerString = $node->toLowerString();
        if ($lowerString === 'string') {
            return new StringType();
        }
        if ($lowerString === 'bool') {
            return new BooleanType();
        }
        if ($lowerString === 'false') {
            return new ConstantBooleanType(\false);
        }
        if ($lowerString === 'true') {
            return new ConstantBooleanType(\true);
        }
        if ($lowerString === 'null') {
            return new NullType();
        }
        if ($lowerString === 'object') {
            return new ObjectWithoutClassType();
        }
        if ($lowerString === 'array') {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($lowerString === 'int') {
            return new IntegerType();
        }
        if ($lowerString === 'iterable') {
            return new IterableType(new MixedType(), new MixedType());
        }
        if ($lowerString === 'float') {
            return new FloatType();
        }
        return new MixedType();
    }
}
