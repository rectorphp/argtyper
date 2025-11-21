<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\ValueObject;

final class VariableNameToType
{
    /**
     * @readonly
     * @var string
     */
    private $variableName;
    /**
     * @readonly
     * @var string
     */
    private $objectType;
    public function __construct(string $variableName, string $objectType)
    {
        $this->variableName = $variableName;
        $this->objectType = $objectType;
    }
    public function getVariableName(): string
    {
        return $this->variableName;
    }
    public function getObjectType(): string
    {
        return $this->objectType;
    }
}
