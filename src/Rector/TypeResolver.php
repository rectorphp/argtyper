<?php

declare(strict_types=1);

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
    public static function resolveTypeNode(string $type): FullyQualified|Identifier
    {
        if (str_starts_with($type, 'object:')) {
            return new FullyQualified(substr($type, 7));
        }

        if (in_array($type, [ArrayType::class, ConstantArrayType::class], true)) {
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
