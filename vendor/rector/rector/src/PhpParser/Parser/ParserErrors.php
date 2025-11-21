<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpParser\Parser;

use Argtyper202511\PHPStan\Parser\ParserErrorsException;
final class ParserErrors
{
    /**
     * @readonly
     * @var string
     */
    private $message;
    /**
     * @readonly
     * @var int
     */
    private $line;
    public function __construct(ParserErrorsException $parserErrorsException)
    {
        $this->message = $parserErrorsException->getMessage();
        $this->line = $parserErrorsException->getAttributes()['startLine'] ?? $parserErrorsException->getLine();
    }
    public function getMessage(): string
    {
        return $this->message;
    }
    public function getLine(): int
    {
        return $this->line;
    }
}
