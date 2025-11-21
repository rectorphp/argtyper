<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\ValueObject;

use Argtyper202511\PhpParser\Node\Arg;
use Argtyper202511\PhpParser\Node\Expr\ArrowFunction;
use Argtyper202511\PhpParser\Node\Expr\Closure;
final class ArgAndFunctionLike
{
    /**
     * @readonly
     * @var \PhpParser\Node\Arg
     */
    private $arg;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction
     */
    private $functionLike;
    /**
     * @param \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction $functionLike
     */
    public function __construct(Arg $arg, $functionLike)
    {
        $this->arg = $arg;
        $this->functionLike = $functionLike;
    }
    public function getArg(): Arg
    {
        return $this->arg;
    }
    /**
     * @return \PhpParser\Node\Expr\Closure|\PhpParser\Node\Expr\ArrowFunction
     */
    public function getFunctionLike()
    {
        return $this->functionLike;
    }
    public function hasParams(): bool
    {
        return $this->functionLike->getParams() !== [];
    }
}
