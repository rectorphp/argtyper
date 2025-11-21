<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\ValueObject;

final class VariableNameToTypeCollection
{
    /**
     * @var VariableNameToType[]
     */
    private $variableNameToType;
    /**
     * @param VariableNameToType[] $variableNameToType
     */
    public function __construct(array $variableNameToType)
    {
        $this->variableNameToType = $variableNameToType;
    }
    public function matchByVariableName(string $variableName): ?\Argtyper202511\Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType
    {
        foreach ($this->variableNameToType as $variableNameToType) {
            if ($variableNameToType->getVariableName() !== $variableName) {
                continue;
            }
            return $variableNameToType;
        }
        return null;
    }
    public function remove(\Argtyper202511\Rector\PHPUnit\CodeQuality\ValueObject\VariableNameToType $matchedNullableVariableNameToType): void
    {
        foreach ($this->variableNameToType as $key => $variableNamesToType) {
            if ($matchedNullableVariableNameToType !== $variableNamesToType) {
                continue;
            }
            unset($this->variableNameToType[$key]);
            break;
        }
    }
}
