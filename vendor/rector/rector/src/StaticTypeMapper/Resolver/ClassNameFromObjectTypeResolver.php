<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\StaticTypeMapper\Resolver;

use Argtyper202511\PHPStan\Type\Type;
final class ClassNameFromObjectTypeResolver
{
    public static function resolve(Type $type) : ?string
    {
        $objectClassNames = $type->getObjectClassNames();
        if (\count($objectClassNames) !== 1) {
            return null;
        }
        return $objectClassNames[0];
    }
}
