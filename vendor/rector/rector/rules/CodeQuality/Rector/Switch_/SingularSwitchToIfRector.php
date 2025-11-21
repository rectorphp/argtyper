<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\Switch_;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\Identical;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Break_;
use Argtyper202511\PhpParser\Node\Stmt\If_;
use Argtyper202511\PhpParser\Node\Stmt\Switch_;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Rector\Renaming\NodeManipulator\SwitchManipulator;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Switch_\SingularSwitchToIfRector\SingularSwitchToIfRectorTest
 */
final class SingularSwitchToIfRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Renaming\NodeManipulator\SwitchManipulator
     */
    private $switchManipulator;
    public function __construct(SwitchManipulator $switchManipulator)
    {
        $this->switchManipulator = $switchManipulator;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change `switch` with only 1 check to `if`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeObject
{
    public function run($value)
    {
        $result = 1;
        switch ($value) {
            case 100:
            $result = 1000;
        }

        return $result;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($value)
    {
        $result = 1;
        if ($value === 100) {
            $result = 1000;
        }

        return $result;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     * @return Node\Stmt[]|If_|null
     */
    public function refactor(Node $node)
    {
        if (count($node->cases) !== 1) {
            return null;
        }
        $onlyCase = $node->cases[0];
        // only default → basically unwrap
        if (!$onlyCase->cond instanceof Expr) {
            // remove default clause because it cause syntax error
            return array_filter($onlyCase->stmts, static function (Stmt $stmt): bool {
                return !$stmt instanceof Break_;
            });
        }
        $if = new If_(new Identical($node->cond, $onlyCase->cond));
        $if->stmts = $this->switchManipulator->removeBreakNodes($onlyCase->stmts);
        return $if;
    }
}
