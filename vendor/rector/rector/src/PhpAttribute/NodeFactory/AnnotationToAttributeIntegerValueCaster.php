<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpAttribute\NodeFactory;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\ArrayItem;
use Argtyper202511\PhpParser\Node\Expr\Array_;
use Argtyper202511\PhpParser\Node\Scalar\Int_;
use Argtyper202511\PhpParser\Node\Scalar\String_;
use Argtyper202511\PHPStan\Reflection\ClassReflection;
use Argtyper202511\PHPStan\Reflection\ParameterReflection;
use Argtyper202511\PHPStan\Reflection\ParametersAcceptorSelector;
use Argtyper202511\PHPStan\Reflection\ReflectionProvider;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\UnionType;
use Argtyper202511\Rector\Php80\ValueObject\AnnotationToAttribute;
use Argtyper202511\RectorPrefix202511\Webmozart\Assert\Assert;
final class AnnotationToAttributeIntegerValueCaster
{
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @param Arg[] $args
     */
    public function castAttributeTypes(AnnotationToAttribute $annotationToAttribute, array $args) : void
    {
        Assert::allIsInstanceOf($args, Arg::class);
        if (!$this->reflectionProvider->hasClass($annotationToAttribute->getAttributeClass())) {
            return;
        }
        $attributeClassReflection = $this->reflectionProvider->getClass($annotationToAttribute->getAttributeClass());
        if (!$attributeClassReflection->hasConstructor()) {
            return;
        }
        $parameterReflections = $this->resolveConstructorParameterReflections($attributeClassReflection);
        foreach ($parameterReflections as $parameterReflection) {
            foreach ($args as $arg) {
                if (!$arg->value instanceof Array_) {
                    continue;
                }
                $arrayItem = \current($arg->value->items) ?: null;
                if (!$arrayItem instanceof ArrayItem) {
                    continue;
                }
                if (!$arrayItem->key instanceof String_) {
                    continue;
                }
                $keyString = $arrayItem->key;
                if ($keyString->value !== $parameterReflection->getName()) {
                    continue;
                }
                // ensure type is casted to integer
                if (!$arrayItem->value instanceof String_) {
                    continue;
                }
                if (!$this->containsInteger($parameterReflection->getType())) {
                    continue;
                }
                $valueString = $arrayItem->value;
                if (!\is_numeric($valueString->value)) {
                    continue;
                }
                $arrayItem->value = new Int_((int) $valueString->value);
            }
        }
    }
    private function containsInteger(Type $type) : bool
    {
        if ($type->isInteger()->yes()) {
            return \true;
        }
        if (!$type instanceof UnionType) {
            return \false;
        }
        foreach ($type->getTypes() as $unionedType) {
            if ($unionedType->isInteger()->yes()) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return ParameterReflection[]
     */
    private function resolveConstructorParameterReflections(ClassReflection $classReflection) : array
    {
        $extendedMethodReflection = $classReflection->getConstructor();
        $extendedParametersAcceptor = ParametersAcceptorSelector::combineAcceptors($extendedMethodReflection->getVariants());
        return $extendedParametersAcceptor->getParameters();
    }
}
