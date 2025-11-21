<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\Php80\ValueObject;

use Argtyper202511\PhpParser\Comment;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\Rector\Php80\Enum\MatchKind;
final class CondAndExpr
{
    /**
     * @var Expr[]|null
     * @readonly
     */
    private $condExprs;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr
     */
    private $expr;
    /**
     * @var MatchKind::*
     * @readonly
     */
    private $matchKind;
    /**
     * @var Comment[]
     * @readonly
     */
    private $comments = [];
    /**
     * @param Expr[]|null $condExprs
     * @param MatchKind::* $matchKind
     * @param Comment[] $comments
     */
    public function __construct(?array $condExprs, Expr $expr, string $matchKind, array $comments = [])
    {
        $this->condExprs = $condExprs;
        $this->expr = $expr;
        $this->matchKind = $matchKind;
        $this->comments = $comments;
    }
    public function getExpr() : Expr
    {
        return $this->expr;
    }
    /**
     * @return Expr[]|null
     */
    public function getCondExprs() : ?array
    {
        // internally checked by PHPStan, cannot be empty array
        if ($this->condExprs === []) {
            return null;
        }
        if ($this->condExprs === null) {
            return null;
        }
        return \array_values($this->condExprs);
    }
    /**
     * @return MatchKind::*
     */
    public function getMatchKind() : string
    {
        return $this->matchKind;
    }
    /**
     * @param MatchKind::* $matchKind
     */
    public function equalsMatchKind(string $matchKind) : bool
    {
        return $this->matchKind === $matchKind;
    }
    /**
     * @return Comment[]
     */
    public function getComments() : array
    {
        return $this->comments;
    }
}
