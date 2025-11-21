<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Removing\ValueObject;

use Argtyper202511\Rector\Validation\RectorAssert;
final class RemoveFuncCallArg
{
    /**
     * @readonly
     * @var string
     */
    private $function;
    /**
     * @readonly
     * @var int
     */
    private $argumentPosition;
    public function __construct(string $function, int $argumentPosition)
    {
        $this->function = $function;
        $this->argumentPosition = $argumentPosition;
        RectorAssert::functionName($function);
    }
    public function getFunction() : string
    {
        return $this->function;
    }
    public function getArgumentPosition() : int
    {
        return $this->argumentPosition;
    }
}
