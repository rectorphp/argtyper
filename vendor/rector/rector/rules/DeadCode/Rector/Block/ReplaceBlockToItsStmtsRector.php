<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\DeadCode\Rector\Block;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Stmt;
use Argtyper202511\PhpParser\Node\Stmt\Block;
use Argtyper202511\PhpParser\NodeVisitor;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\Block\ReplaceBlockToItsStmtsRector\ReplaceBlockToItsStmtsRectorTest
 * @see https://3v4l.org/ZUfEV
 */
final class ReplaceBlockToItsStmtsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace Block Stmt with its stmts', [new CodeSample(<<<'CODE_SAMPLE'
{
    echo "statement 1";
    echo PHP_EOL;
    echo "statement 2";
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo "statement 1";
echo PHP_EOL;
echo "statement 2";
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Block::class];
    }
    /**
     * @param Block $node
     * @return int|Stmt[]
     */
    public function refactor(Node $node)
    {
        if ($node->stmts === []) {
            return NodeVisitor::REMOVE_NODE;
        }
        return $node->stmts;
    }
}
