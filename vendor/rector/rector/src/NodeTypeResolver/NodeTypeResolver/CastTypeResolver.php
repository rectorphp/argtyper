<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeTypeResolver\NodeTypeResolver;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Cast;
use Argtyper202511\PhpParser\Node\Expr\Cast\Array_;
use Argtyper202511\PhpParser\Node\Expr\Cast\Bool_;
use Argtyper202511\PhpParser\Node\Expr\Cast\Double;
use Argtyper202511\PhpParser\Node\Expr\Cast\Int_;
use Argtyper202511\PhpParser\Node\Expr\Cast\Object_;
use Argtyper202511\PhpParser\Node\Expr\Cast\String_;
use Argtyper202511\PHPStan\Type\ArrayType;
use Argtyper202511\PHPStan\Type\BooleanType;
use Argtyper202511\PHPStan\Type\FloatType;
use Argtyper202511\PHPStan\Type\IntegerType;
use Argtyper202511\PHPStan\Type\MixedType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\StringType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\Exception\NotImplementedYetException;
use Argtyper202511\Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @implements NodeTypeResolverInterface<Cast>
 */
final class CastTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @var array<class-string<Node>, class-string<Type>>
     */
    private const CAST_CLASS_TO_TYPE_MAP = [Bool_::class => BooleanType::class, String_::class => StringType::class, Int_::class => IntegerType::class, Double::class => FloatType::class];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Cast::class];
    }
    /**
     * @param Cast $node
     */
    public function resolve(Node $node) : Type
    {
        foreach (self::CAST_CLASS_TO_TYPE_MAP as $castClass => $typeClass) {
            if ($node instanceof $castClass) {
                return new $typeClass();
            }
        }
        if ($node instanceof Array_) {
            return new ArrayType(new MixedType(), new MixedType());
        }
        if ($node instanceof Object_) {
            return new ObjectType('stdClass');
        }
        throw new NotImplementedYetException(\get_class($node));
    }
}
