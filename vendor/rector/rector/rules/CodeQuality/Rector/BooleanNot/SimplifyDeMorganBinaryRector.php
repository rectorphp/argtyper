<?php

declare (strict_types=1);
namespace Argtyper202511\Rector\CodeQuality\Rector\BooleanNot;

use Argtyper202511\PhpParser\Node;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp;
use Argtyper202511\PhpParser\Node\Expr\BinaryOp\BooleanOr;
use Argtyper202511\PhpParser\Node\Expr\BooleanNot;
use Argtyper202511\Rector\NodeManipulator\BinaryOpManipulator;
use Argtyper202511\Rector\Rector\AbstractRector;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Argtyper202511\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\BooleanNot\SimplifyDeMorganBinaryRector\SimplifyDeMorganBinaryRectorTest
 */
final class SimplifyDeMorganBinaryRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\BinaryOpManipulator
     */
    private $binaryOpManipulator;
    public function __construct(BinaryOpManipulator $binaryOpManipulator)
    {
        $this->binaryOpManipulator = $binaryOpManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Simplify negated conditions with de Morgan theorem', [new CodeSample(<<<'CODE_SAMPLE'
$a = 5;
$b = 10;
$result = !($a > 20 || $b <= 50);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$a = 5;
$b = 10;
$result = $a <= 20 && $b > 50;
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [BooleanNot::class];
    }
    /**
     * @param BooleanNot $node
     */
    public function refactor(Node $node) : ?BinaryOp
    {
        if (!$node->expr instanceof BooleanOr) {
            return null;
        }
        return $this->binaryOpManipulator->inverseBooleanOr($node->expr);
    }
}
