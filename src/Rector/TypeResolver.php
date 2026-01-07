<?php

declare (strict_types=1);
namespace Rector\ArgTyper\Rector;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantArrayType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\StringType;
use Rector\ArgTyper\Exception\NotImplementedException;
final class TypeResolver
{
    /**
     * @return \PhpParser\Node\Name\FullyQualified|\PhpParser\Node\Identifier
     */
    public static function resolveTypeNode(string $type)
    {
        if (strncmp($type, 'object:', strlen('object:')) === 0) {
            return new FullyQualified((string) substr($type, 7));
        }
        if (in_array($type, ['array', ArrayType::class, ConstantArrayType::class], \true)) {
            return new Identifier('array');
        }
        if (strncmp($type, 'array', strlen('array')) === 0) {
            return new Identifier('array');
        }
        if ($type === StringType::class) {
            return new Identifier('string');
        }
        if ($type === IntegerType::class) {
            return new Identifier('int');
        }
        if ($type === FloatType::class) {
            return new Identifier('float');
        }
        if ($type === BooleanType::class) {
            return new Identifier('bool');
        }
        throw new NotImplementedException($type);
    }
}
