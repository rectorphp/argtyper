<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PhpParser\ValueObject;

use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Token;
final class StmtsAndTokens
{
    /**
     * @var Stmt[]
     * @readonly
     */
    private $stmts;
    /**
     * @var array<int, Token>
     * @readonly
     */
    private $tokens;
    /**
     * @param Stmt[] $stmts
     * @param array<int, Token> $tokens
     */
    public function __construct(array $stmts, array $tokens)
    {
        $this->stmts = $stmts;
        $this->tokens = $tokens;
    }
    /**
     * @return Stmt[]
     */
    public function getStmts(): array
    {
        return $this->stmts;
    }
    /**
     * @return array<int, Token>
     */
    public function getTokens(): array
    {
        return $this->tokens;
    }
}
