<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\Variable;
final class VariableAndExprAssign
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Variable
     */
    private $variable;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    public function __construct(Variable $variable, Expr $expr)
    {
        $this->variable = $variable;
        $this->expr = $expr;
    }
    public function getVariable(): Variable
    {
        return $this->variable;
    }
    public function getExpr(): Expr
    {
        return $this->expr;
    }
}
