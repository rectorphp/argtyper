<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPStanStaticTypeMapper\Utils;

use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\TypeCombinator;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class TypeUnwrapper
{
    public function unwrapFirstObjectTypeFromUnionType(Type $type): Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $unionedType) {
            $className = ClassNameFromObjectTypeResolver::resolve($unionedType);
            if ($className === null) {
                continue;
            }
            return $unionedType;
        }
        return $type;
    }
    public function unwrapFirstCallableTypeFromUnionType(Type $type): Type
    {
        if (!$type instanceof UnionType) {
            return $type;
        }
        foreach ($type->getTypes() as $unionedType) {
            if (!$unionedType->isCallable()->yes()) {
                continue;
            }
            return $unionedType;
        }
        return $type;
    }
    public function isIterableTypeValue(string $className, Type $type): bool
    {
        $typeClassName = ClassNameFromObjectTypeResolver::resolve($type);
        if ($typeClassName === null) {
            return \false;
        }
        // get the namespace from $className
        $classNamespace = $this->namespace($className);
        // get the namespace from $parameterReflection
        $reflectionNamespace = $this->namespace($typeClassName);
        // then match with
        return $reflectionNamespace === $classNamespace && substr_compare($typeClassName, 'TValue', -strlen('TValue')) === 0;
    }
    public function isIterableTypeKey(string $className, Type $type): bool
    {
        $typeClassName = ClassNameFromObjectTypeResolver::resolve($type);
        if ($typeClassName === null) {
            return \false;
        }
        // get the namespace from $className
        $classNamespace = $this->namespace($className);
        // get the namespace from $parameterReflection
        $reflectionNamespace = $this->namespace($typeClassName);
        // then match with
        return $reflectionNamespace === $classNamespace && substr_compare($typeClassName, 'TKey', -strlen('TKey')) === 0;
    }
    public function removeNullTypeFromUnionType(UnionType $unionType): Type
    {
        return TypeCombinator::removeNull($unionType);
    }
    private function namespace(string $class): string
    {
        return implode('\\', array_slice(explode('\\', $class), 0, -1));
    }
}
