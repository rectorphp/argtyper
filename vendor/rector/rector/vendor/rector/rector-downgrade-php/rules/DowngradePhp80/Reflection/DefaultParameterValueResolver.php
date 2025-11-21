<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DowngradePhp80\Reflection;

use Argtyper202511\PhpParser\BuilderHelpers;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\ConstFetch;
use Argtyper202511\PhpParser\Node\Name;
use Argtyper202511\PHPStan\Reflection\ParameterReflection;
use Argtyper202511\PHPStan\Type\Constant\ConstantArrayType;
use Argtyper202511\PHPStan\Type\Constant\ConstantBooleanType;
use Argtyper202511\PHPStan\Type\Constant\ConstantIntegerType;
use Argtyper202511\PHPStan\Type\Constant\ConstantStringType;
use Argtyper202511\PHPStan\Type\NullType;
use Argtyper202511\PHPStan\Type\Type;
use Argtyper202511\PHPStan\Type\VerbosityLevel;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
final class DefaultParameterValueResolver
{
    public function resolveFromParameterReflection(ParameterReflection $parameterReflection): ?\Argtyper202511\PhpParser\Node\Expr
    {
        $defaultValueType = $parameterReflection->getDefaultValue();
        if (!$defaultValueType instanceof Type) {
            return null;
        }
        if (!$defaultValueType->isConstantValue()->yes()) {
            throw new ShouldNotHappenException();
        }
        return $this->resolveValueFromType($defaultValueType);
    }
    /**
     * @return \PhpParser\Node\Expr\ConstFetch|\PhpParser\Node\Expr
     */
    private function resolveValueFromType(Type $constantType)
    {
        if ($constantType instanceof ConstantBooleanType) {
            return $this->resolveConstantBooleanType($constantType);
        }
        if ($constantType instanceof ConstantArrayType) {
            $values = [];
            foreach ($constantType->getValueTypes() as $valueType) {
                if (!$valueType->isConstantValue()->yes()) {
                    throw new ShouldNotHappenException();
                }
                $values[] = $this->resolveValueFromType($valueType);
            }
            return BuilderHelpers::normalizeValue($values);
        }
        /** @var ConstantStringType|ConstantIntegerType|NullType $constantType */
        return BuilderHelpers::normalizeValue($constantType->getValue());
    }
    private function resolveConstantBooleanType(ConstantBooleanType $constantBooleanType): ConstFetch
    {
        $value = $constantBooleanType->describe(VerbosityLevel::value());
        return new ConstFetch(new Name($value));
    }
}
