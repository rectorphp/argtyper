<?php

declare (strict_types=1);
namespace Argtyper202511\PHPStan\PhpDocParser\Ast\Type;

use Argtyper202511\PHPStan\PhpDocParser\Ast\NodeAttributes;
use Argtyper202511\PHPStan\PhpDocParser\Parser\ParserException;
class InvalidTypeNode implements \Argtyper202511\PHPStan\PhpDocParser\Ast\Type\TypeNode
{
    use NodeAttributes;
    /** @var mixed[] */
    private $exceptionArgs;
    public function __construct(ParserException $exception)
    {
        $this->exceptionArgs = [$exception->getCurrentTokenValue(), $exception->getCurrentTokenType(), $exception->getCurrentOffset(), $exception->getExpectedTokenType(), $exception->getExpectedTokenValue(), $exception->getCurrentTokenLine()];
    }
    public function getException() : ParserException
    {
        return new ParserException(...$this->exceptionArgs);
    }
    public function __toString() : string
    {
        return '*Invalid type*';
    }
}
