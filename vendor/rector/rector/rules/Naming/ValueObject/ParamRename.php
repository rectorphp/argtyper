<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Naming\ValueObject;

use Argtyper202511\PhpParser\Node\Expr\Variable;
use Argtyper202511\PhpParser\Node\FunctionLike;
final class ParamRename
{
    /**
     * @readonly
     * @var string
     */
    private $currentName;
    /**
     * @readonly
     * @var string
     */
    private $expectedName;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @readonly
     * @var \PhpParser\Node\FunctionLike
     */
    private $functionLike;
    public function __construct(string $currentName, string $expectedName, Variable $variable, FunctionLike $functionLike)
    {
        $this->currentName = $currentName;
        $this->expectedName = $expectedName;
        $this->variable = $variable;
        $this->functionLike = $functionLike;
    }
    public function getCurrentName(): string
    {
        return $this->currentName;
    }
    public function getExpectedName(): string
    {
        return $this->expectedName;
    }
    public function getFunctionLike(): FunctionLike
    {
        return $this->functionLike;
    }
    public function getVariable(): Variable
    {
        return $this->variable;
    }
}
