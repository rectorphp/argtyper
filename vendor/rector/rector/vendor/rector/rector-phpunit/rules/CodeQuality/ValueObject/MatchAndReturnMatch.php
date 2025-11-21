<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\PHPUnit\CodeQuality\ValueObject;

use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\CallLike;
use Argtyper202511\PhpParser\Node\Expr\Match_;
use Argtyper202511\Rector\Exception\ShouldNotHappenException;
final class MatchAndReturnMatch
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Match_
     */
    private $consecutiveMatch;
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Match_|null
     */
    private $willReturnMatch;
    public function __construct(Match_ $consecutiveMatch, ?Match_ $willReturnMatch)
    {
        $this->consecutiveMatch = $consecutiveMatch;
        $this->willReturnMatch = $willReturnMatch;
    }
    public function getConsecutiveMatch() : Match_
    {
        return $this->consecutiveMatch;
    }
    public function getConsecutiveMatchExpr() : Expr
    {
        $soleArm = $this->consecutiveMatch->arms[0];
        if ($soleArm->body instanceof CallLike) {
            $assertCall = $soleArm->body;
            $firstArg = $assertCall->getArgs()[0];
            return $firstArg->value;
        }
        throw new ShouldNotHappenException();
    }
    public function getWillReturnMatch() : ?Match_
    {
        return $this->willReturnMatch;
    }
    public function getWillReturnMatchExpr() : Expr
    {
        if (!$this->willReturnMatch instanceof Match_) {
            throw new ShouldNotHappenException();
        }
        $soleArm = $this->willReturnMatch->arms[0];
        return $soleArm->body;
    }
}
