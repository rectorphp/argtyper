<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\ValueObject;

use Argtyper202511\PHPStan\Type\Type;
final class ParamTypesAndReturnType
{
    /**
     * @var Type[]
     * @readonly
     */
    private $paramTypes;
    /**
     * @readonly
     * @var \PHPStan\Type\Type|null
     */
    private $returnType;
    /**
     * @param Type[] $paramTypes
     */
    public function __construct(array $paramTypes, ?Type $returnType)
    {
        $this->paramTypes = $paramTypes;
        $this->returnType = $returnType;
    }
    /**
     * @return Type[]
     */
    public function getParamTypes(): array
    {
        return $this->paramTypes;
    }
    public function getReturnType(): ?Type
    {
        return $this->returnType;
    }
}
