<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\NodeNestingScope\ValueObject;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\Match_;
use Argtyper202511\PhpParser\Node\Stmt\Case_;
use Argtyper202511\PhpParser\Node\Stmt\Catch_;
use Argtyper202511\PhpParser\Node\Stmt\Do_;
use Argtyper202511\PhpParser\Node\Stmt\Else_;
use Argtyper202511\PhpParser\Node\Stmt\ElseIf_;
use Argtyper202511\PhpParser\Node\Stmt\Foreach_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\PhpParser\Node\Stmt\While_;
final class ControlStructure
{
    /**
     * These situations happens only if condition is met
     * @var array<class-string<Node>>
     */
    public const CONDITIONAL_NODE_SCOPE_TYPES = [If_::class, While_::class, Do_::class, Else_::class, ElseIf_::class, Catch_::class, Case_::class, Match_::class, Switch_::class, Foreach_::class];
}
