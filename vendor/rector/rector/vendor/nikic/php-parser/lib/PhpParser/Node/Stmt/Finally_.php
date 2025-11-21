<?php

declare (strict_types=1);
namespace Argtyper202511\PhpParser\Node\Stmt;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\Rector\Contract\PhpParser\Node\StmtsAwareInterface;
class Finally_ extends Node\Stmt implements StmtsAwareInterface
{
    /** @var Node\Stmt[] Statements */
    public $stmts;
    /**
     * Constructs a finally node.
     *
     * @param Node\Stmt[] $stmts Statements
     * @param array<string, mixed> $attributes Additional attributes
     */
    public function __construct(array $stmts = [], array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->stmts = $stmts;
    }
    public function getSubNodeNames(): array
    {
        return ['stmts'];
    }
    public function getType(): string
    {
        return 'Stmt_Finally';
    }
}
