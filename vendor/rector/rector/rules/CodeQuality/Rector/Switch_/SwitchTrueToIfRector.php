<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Switch_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Case_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Return_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\Rector\PhpParser\Node\Value\ValueResolver;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Switch_\SwitchTrueToIfRector\SwitchTrueToIfRectorTest
 */
final class SwitchTrueToIfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `switch (true)` to `if` statements', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        switch (true) {
            case $value === 0:
                return 'no';
            case $value === 1:
                return 'yes';
            case $value === 2:
                return 'maybe';
        };
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        if ($value === 0) {
            return 'no';
        }

        if ($value === 1) {
            return 'yes';
        }

        if ($value === 2) {
            return 'maybe';
        }
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     * @return Stmt[]|null
     */
    public function refactor(Node $node) : ?array
    {
        if (!$this->valueResolver->isTrue($node->cond)) {
            return null;
        }
        $newStmts = [];
        $defaultCase = null;
        foreach ($node->cases as $case) {
            if (!\end($case->stmts) instanceof Return_) {
                return null;
            }
            if (!$case->cond instanceof Expr) {
                $defaultCase = $case;
                continue;
            }
            $if = new If_($case->cond);
            $if->stmts = $case->stmts;
            $newStmts[] = $if;
        }
        if ($defaultCase instanceof Case_) {
            $newStmts = \array_merge($newStmts, $defaultCase->stmts);
        }
        if ($newStmts === []) {
            return null;
        }
        return $newStmts;
    }
}
