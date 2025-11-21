<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\TypeDeclaration\TypeInferer;

use Argtyper202511\PHPStan\Type\Generic\GenericObjectType;
use Argtyper202511\PHPStan\Type\ObjectType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\Rector\StaticTypeMapper\Resolver\ClassNameFromObjectTypeResolver;
final class SplArrayFixedTypeNarrower
{
    public function narrow(Type $paramType): Type
    {
        if ($paramType->isSuperTypeOf(new ObjectType('SplFixedArray'))->no()) {
            return $paramType;
        }
        $className = ClassNameFromObjectTypeResolver::resolve($paramType);
        if ($className === null) {
            return $paramType;
        }
        if ($paramType instanceof GenericObjectType) {
            return $paramType;
        }
        $types = [];
        if ($className === 'PhpCsFixer\Tokenizer\Tokens') {
            $types[] = new ObjectType('Argtyper202511\PhpCsFixer\Tokenizer\Token');
        }
        if ($className === 'PhpCsFixer\Doctrine\Annotation\Tokens') {
            $types[] = new ObjectType('Argtyper202511\PhpCsFixer\Doctrine\Annotation\Token');
        }
        if ($types === []) {
            return $paramType;
        }
        return new GenericObjectType($className, $types);
    }
}
